
var URL_GET_CERTIFICATES = "/js/sign/CACertificates.p7b";

var signer = {
	euSign: EUSignCP(),
	isInitialized: false,

	initialize: function() {
		if (this.isInitialized) return;

		this.euSign.Initialize();
		this.euSign.SetJavaStringCompliant(true);
		this.euSign.SetCharset("UTF-16LE");

		if (this.euSign.DoesNeedSetSettings()) {
			var settings = this.euSign.CreateFileStoreSettings();
			settings.SetPath("/certificates");
			settings.SetSaveLoadedCerts(true);
			this.euSign.SetFileStoreSettings(settings);

			settings = this.euSign.CreateProxySettings();
			this.euSign.SetProxySettings(settings);
			
			settings = this.euSign.CreateTSPSettings();
			this.euSign.SetTSPSettings(settings);

			settings = this.euSign.CreateOCSPSettings();
			this.euSign.SetOCSPSettings(settings);

			settings = this.euSign.CreateCMPSettings();
			this.euSign.SetCMPSettings(settings);

			settings = this.euSign.CreateLDAPSettings();
			this.euSign.SetLDAPSettings(settings);
			
			settings = this.euSign.CreateOCSPAccessInfoModeSettings();
			settings.SetEnabled(true);
			this.euSign.SetOCSPAccessInfoModeSettings(settings);
		}

		this.isInitialized = true;
	},

	/* 
	 * Loads CA bundle from server 
	 */
	loadCA: function() {
		var that = this;

		// return a promise that will load CAs file
		return new Promise(function(resolve, reject) {
			that.euSign.LoadDataFromServer(URL_GET_CERTIFICATES, function(data) {
					console.log("Loaded CAs");
					
					try {
						that.euSign.SaveCertificates(data);
					} catch (e) {
						reject("Failed to process CAs", e);
						return;
					}

					resolve();

				}, function(e) {
					console.error("Failed to load CAs", e);
					reject(e);
				}, true);
		});
	},

	/*
	 * Loads user's certificate file
	 *
	 * @param cert 			file to load (like input.files[0])
	 */
	loadCertificate: function(cert) {
		var that = this;

		if (!cert)
			throw new Error("Invalid arg");

		return new Promise(function(resolve, reject) {
			that.euSign.ReadFile(cert, function(file) {
				console.log("Loaded certificate file");

				try {
					that.euSign.SaveCertificate(file.data);
				} catch(e) {
					console.error("Failed to process certificate file", e);
					reject(e);
					return;
				}

				resolve();

			}, function(e) {
				console.log("Failed to load certificate file", e);
				reject(e);
			});
		});
	},

	/*
	 * Loads user's private key file
	 *
	 * @param key 			file to load (like input.files[0])
	 * @param password 		key password (string)
	 */
	loadKey: function(key, password) {
		var that = this;

		if (!key || !password)
			throw new Error("Invalid arg");

		return new Promise(function(resolve, reject) {
			that.euSign.ReadFile(key, function(file) {
				console.log("Loaded private key file");

				try {
					that.euSign.ReadPrivateKeyBinary(file.data, password);
				} catch(e) {
					console.error("Failed to process private key file", e);
					reject(e);
					return;
				}

				resolve();

			}, function(e) {
				console.log("Failed to load private key file", e);
				reject(e);
			});
		});
	},

	/*
	 * Validates input and formats document url for signing
	 *
	 * @param documentType 		type of document ("tender" or "plan")
	 * @param documentId 		id of document (numeric string)
	 *
	 * @result 					relative document url
	 */
	documentUrl: function(documentType, documentId) {
		// if (documentType !== "tender" && documentType !== "plan")
		// 	throw new Error("Invalid document type");

		if (!documentId || !/^\d+$/.test(documentId))
			throw new Error("Invalid document id");

		return "/" + documentType + "/" + documentId + "/sign";
	},

	/*
	 * Loads document to sign/verify from backend
	 * 
	 * @param documentType 		type of document ("tender" or "plan")
	 * @param documentId 		id of document (numeric string)
	 *
	 * @result 					object { documentData: string, signature: base64-string }
	 */
	loadDocument: function(documentType, documentId) {
		var that = this;

		var url = this.documentUrl(documentType, documentId);

		return new Promise(function(resolve, reject) {
			$.ajax({
				url: url,
				method: "GET",
				dataType: "json",

				success: function(data) {
					console.log(data);
					console.log("Loaded document");

					signature = data.sign;
					data = data.data;

					if (!data) {
						reject(new Error("Invalid document data"));
						return;
					}
					
					// strip document actual data from unnecessary fields
					var documentData = prepareObject(data);

					resolve({ documentData: documentData, signature: signature });
				},

				error: function(error) {
					console.error("Failed to load document", error);
					reject(error);
				}
			})
		});
	},

	/*
	 * Stores signature for specified document at backend
	 * 
	 * @param documentType 		type of document ("tender" or "plan")
	 * @param documentId		id of document (numeric string)
	 * @param signature 		signature base64-string
	 */
	updateDocument: function(documentType, documentId, signature) {
		var that = this;

		var url = this.documentUrl(documentType, documentId);

		return new Promise(function(resolve, reject) {
			$.ajax({
				url: url,
				method: "POST",
				data: { sign: signature },

				success: function(data) {
					console.log("Stored signature");
					resolve();
				},

				error: function(error) {
					console.error("Failed to store signature", error);
					reject(e);
				}
			});
		});
	},

	/*
	 * Generates a signature for specified document data
	 *
	 * @param documentData 		content to sign (result of prepareObject() call)
	 *
	 * @result 					signature base64-string
	 */
	sign: function(documentData) {
		var that = this;

		if (!documentData)
			throw new Error("Invalid arg");

		return new Promise(function(resolve, reject) {

			var r;
			try {
				r = that.euSign.SignDataInternal(true /* add cert */, documentData, true /* return base64 string */);
			} catch(e) {
				console.error("Failed to sign data", e);
				reject(e);
				return;
			}

			resolve(r);
		});
	},

	/*
	 * Verifies a signature for specified document data
	 *
	 * @param documentData 		content to verify (result of prepareObject() call)
	 * @param signature 		signature to verify (base64-string)
	 * 
	 * @result 					object containing signer's information, or false if signature is invalid
	 */
	verify: function(documentData, signature) {
		var that = this;

		// return a promise that will validate
		return new Promise(function(resolve, reject) {

			if (!signature) {
				console.log("Document is not signed");
				resolve(null);
				return;
			}

			var r;
			try {
				r = that.euSign.VerifyDataInternal(signature);
			} catch(e) {
				console.error("Signature is corrupted", e);
				reject(e);
				return;
			}

			// signature is valid, and matches internal data blob
			// compare internal blob with content from document

			var internalData = JSON.parse(that.euSign.ArrayToString(r.GetData()));
			
			// workaround cases when sign json with "data" key
			if (internalData.hasOwnProperty('data')) {
				internalData = internalData.data;
			}

			// strip signature data from unnecessary fields
			internalData = prepareObject(internalData);

			// compare two objects
			var delta = jsondiffpatch.diff(JSON.parse(internalData), JSON.parse(documentData));
			if (delta) {
				console.error("Invalid signature");
				console.log("Document data: ", documentData);
				console.log("Internal data: ", internalData);
				// signed data and actual data doesn't match, error
				resolve(false);
			} else {
				console.log("Signature is valid");
				resolve(r);
			}
		});
	}
};




/*
 * Signs the document
 *
 * @param documentType 		document type ("tender" or "plan")
 * @param documentId 		document id
 * @param certFile 			certificate file info (like input.files[0])
 * @param keyFile 			private key file info
 * @param password 			private key password
 * @param successCallback 	function to call when signed successfully
 * @param failureCallback 	function to call when signing failed
 * 							takes single arg (error object)
 */
function SignDocument(documentType, documentId, certFile, keyFile, password, successCallback, failureCallback) {
	// initialize signer
	signer.initialize();

	// start signing chain
	signer.loadCA()
	.then(function() {
		return signer.loadCertificate(certFile);
	})
	.then(function() {
		return signer.loadKey(keyFile, password);
	})
	.then(function() {
		return signer.loadDocument(documentType, documentId);
	})
	.then(function(info) {
		var documentData = info.documentData;

		return signer.sign(documentData);
	})
	.then(function(signature) {
		return signer.updateDocument(documentType, documentId, signature);
	})
	.then(function() {
		console.log("Signature update done");

		successCallback();
	})
	['catch'](function(e) {
		// catch is a reserved keyword on old IE, so using ['catch'] instead

		console.log("Signature update failed");

		failureCallback(e);
	});
}

/*
 * Validates signature for document
 *
 * @param documentType 		document type ("tender" or "plan")
 * @param documentId 		document id
 * @param successCallback 	function to call when signature is valid 
 * 							takes single arg (signature info, or false if signature is invalid, 
 * 							or null if document is not signed)
 * @param failureCallback 	function to call when signature is invalid
 * 							takes single arg (error object)
 */
function VerifyDocument(documentType, documentId, successCallback, failureCallback) {
	// initialize signer
	signer.initialize();

	// start verification chain
	signer.loadCA()
	.then(function() {
		return signer.loadDocument(documentType, documentId);
	})
	.then(function(info) {
		var documentData = info.documentData;
		var signature = info.signature;

		return signer.verify(documentData, signature);
	})
	.then(function(info) {
		console.log("Signature verification done");
		
		successCallback(info);
	})
	['catch'](function(e) {
		// catch is a reserved keyword on old IE, so using ['catch'] instead
		console.log("Signature verification failed");
		
		failureCallback(e);
	});
}



/*
// Prepare object for sign
// taken from https://github.com/openprocurement-crypto/share/
function prepareObject(json_object) {
    var fields = ['documents', 'items', 'lots', 'features', 'enquiryPeriod', 'tenderPeriod',
        'procuringEntity', 'title', 'title_en', 'title_ru', 'description', 'description_ru',
        'description_en', 'value', 'minimalStep', 'procurementMethod', 'procurementMethodType',
        'id', 'tenderID', 'cause', 'causeDescription', 'guarantee',
        // for plan
        'additionalClassifications', 'budget', 'classification', 'planID', 'tender',
        // for awards
        'suppliers', 'bid_id', 'qualified', 'eligible', 'lotID',
        // for contracts
        'awardID', 'contractID', 'dateSigned'
    ];
    var result = {};
    for (var i = 0; i < fields.length; i++) {
        if (json_object[fields[i]] !== undefined) result[fields[i]] = json_object[fields[i]];
    }
    // temporary region_id
    if (result.items && result.items.length) {
        for (var i = 0; i < result.items.length; i++) {
            if (result.items[i].hasOwnProperty('region_id')) delete result.items[i]['region_id'];
        }
    }
    // remove enquiryPeriod.clarificationsUntil
    if (result.enquiryPeriod && result.enquiryPeriod.hasOwnProperty('clarificationsUntil'))
        delete result.enquiryPeriod['clarificationsUntil'];
    // remove enquiryPeriod.invalidationDate
    if (result.enquiryPeriod && result.enquiryPeriod.hasOwnProperty('invalidationDate'))
        delete result.enquiryPeriod['invalidationDate'];

    //remove procuringEntity.contactPoint.Language
    if (result.procuringEntity && result.procuringEntity.contactPoint && result.procuringEntity.contactPoint.hasOwnProperty('Language'))
        delete result.procuringEntity.contactPoint['Language'];

    // fix amount like 142613.33000000002 in CDB
    if (result.value && result.value.amount) {
        var roundedAmount = Math.round(result.value.amount * 100) / 100;
        if (result.value.amount !== roundedAmount) result.value.amount = roundedAmount;
    }
    // remove fields from documents section
    var documentFields = ['documents', 'financialDocuments', 'eligibilityDocuments', 'qualificationDocuments'];
    for (var i = 0; i < documentFields.length; i++) {
        if (json_object[documentFields[i]] && result[documentFields[i]]) {
            for (var index = json_object[documentFields[i]].length - 1; index >= 0; index--) {
                var document = json_object[documentFields[i]][index];
                // remove start address from url, because different API maybe uses
                var url = document.url.toLowerCase();
                document.url = document.url.replace(/^http(s)?:\/\/[^\/]+\/api\/[^\/]+/i, '');
                // remove Confidentiality and Language
                if (document.hasOwnProperty('Confidentiality')) delete document['Confidentiality'];
                if (document.hasOwnProperty('Language')) delete document['Language'];
                // convert dates to one format (without ms)
                if (document.datePublished && document.datePublished.indexOf('.') > 0)
                    document.datePublished = document.datePublished.substr(0, document.datePublished.indexOf('.'));
                if (document.dateModified && document.dateModified.indexOf('.') > 0)
                    document.dateModified = document.dateModified.substr(0, document.dateModified.indexOf('.'));
                if (document.title === 'sign.p7s' && document.format === "application/pkcs7-signature" || (/^audit_.+\.yaml$/i).test(document.title)) {
                    result[documentFields[i]].splice(index, 1);
                }
            }
            if (!result[documentFields[i]].length) delete result[documentFields[i]];
        }
    }

    if (result.lots && result.lots.length) {
        for (var i = 0; i < result.lots.length; i++) {
            if (result.lots[i].auctionPeriod) delete result.lots[i]['auctionPeriod'];
            if (result.lots[i].auctionUrl) delete result.lots[i]['auctionUrl'];
            if (result.lots[i].status === "active" || result.lots[i].status === "complete" || result.lots[i].status === "unsuccessful") delete result.lots[i]['status'];
        }
    }
    return JSON.stringify(result);
}
*/