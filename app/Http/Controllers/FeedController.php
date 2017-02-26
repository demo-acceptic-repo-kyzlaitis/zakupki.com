<?php

namespace App\Http\Controllers;

use App\Api\Api;
use App\Model\Lot;
use App\Model\PostalCode;
use App\Model\Tender;
use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class FeedController extends Controller {

	/*
	 * TODO регион не определяем на фиде
	 *  причина по которй не обределяеться регион на фиди в том что в базе храняться полные индексы, а нужно хранить только первые 2-3 цыфры
	 *  что бы определить регион, а не точный населенный пункт
	 * */
	/**
	 * Display the specified resource by id, but only tenders.
	 *
	 * @param  int $id
	 *
	 * @return Response
	 */
	public function show( $id ) {
		$tender = Tender::findOrFail( $id );

		/*
		 * Дополнительные контейнеры для комфортной работы с массивами данных
		 * полученных от фида Prozorro
		 * Инициализцыя пустым массивом
		 * */
		$ProzorroFeedGuaranteeByTender = [
			/*
			 * Содержит в себе два ассацыативных ключа
			 * amount, currency
			 * */
		];
		$ProzorroFeedGuaranteeByLots   = [
			/*
			 * Содержит массив значений, вид пока не определен
			 * */
		];
		/*
		 *  Создаем http клиента для получения недостающих данных по тендеру с ЦБД Prozorro
		 * */
		$сdbHttpClient = new Api( false );
		/*
		 *  Подтягиваем данные от ЦБД  на тот случай если в базе чего то не хватает
		 * */
		$ProzorroFeed = $сdbHttpClient->get( $tender->cbd_id );
		/*
		 * Проверка есть ли результат ответа от ЦБД Prozorro
		 * */
		if ( ! empty( $ProzorroFeed ) && ! empty( $ProzorroFeed['data'] ) ) {
			$ProzorroFeedCollection = collect( $ProzorroFeed['data'] );

			/*
			 * Проверяем существование тендерного обеспичения в самаом тендере
			 * */
			if ( $ProzorroFeedCollection->has( 'guarantee' ) ) {
				$ProzorroFeedGuaranteeByTender = $ProzorroFeedCollection->get( 'guarantee' );
			}
			/*
			 * проверяем есть ли массив лотов в тендере, отложено до появления тендера имеющего гарантию для каждого лота
			 * */
			if ( false && $ProzorroFeedCollection->has( 'lots' ) ) {
				/*
				 * Вытаскиваем лоты и переопредиляем тип ключа с индексного (0,1,2...) на id лота от ЦБД Prozorro
				 * */
				$lotsCollectedById = collect( $ProzorroFeedCollection->get( 'lots' ) )->keyBy( 'id' );

				$lotsCollectedById->each( function ( $item, $index ) {
					/*
					 * Вытащить данные по гарантии
					 * */
				} );
			}


		}

		preg_match( '/UA-(\d\d\d\d-\d\d-\d\d)-(.*)/', $tender->tenderID, $matches );
		$dateCreated = $matches[1];

		/*
		 * Опредиление типа закупки (допороговые, после пороговые --- государственные , европейские )
		 * */
		switch ( $tender->type_id ) {
			case 1:
				$tender_type_proc = 'e-auction';
				break;
			case 2:
				$tender_type_proc = 'open-ukr-prozorro';
				break;
			case 3:
				$tender_type_proc = 'open-euro-prozorro';
				break;
			default:
				$tender_type_proc = '';
		}

		$schema = [
			'tender_created_date' => Carbon::parse( $dateCreated )->format( 'Y-m-d H:i:s' ),
			'tender_un_id'        => $tender->tenderID,
			'tender_vesnik_id'    => Carbon::parse( $dateCreated )->format( 'Y-m-d H:i:s' ),
			'tender_vesnik_date'  => Carbon::parse( $dateCreated )->format( 'Y-m-d H:i:s' ),
			'tender_mes_id'       => $tender->tenderID,
			'tender_type'         => '1',

			/*
			 * Статус тендера, так как пока нет протоколов и результатов, то объявить тенер завершонным
			 * можно только через статусы
			 * */
			'status'              => $tender->status,

			'tender_type_proc' => $tender_type_proc,
		];

		if ( ! empty( $ProzorroFeedGuaranteeByTender ) && ! empty( $ProzorroFeedGuaranteeByTender['amount'] ) ) {
			$schema['tender_guarantee_amount']   = $ProzorroFeedGuaranteeByTender['amount'];
			$schema['tender_guarantee_currency'] = $ProzorroFeedGuaranteeByTender['currency'];
		}

		/*$url = route('tender.showByID', ['TenderID' => $tender->tenderID]);*/
		$url = "https://etp.zakupki.com.ua/v?TenderID={$tender->tenderID}";

		$lots               = [];
		$delivery_addresses = [];


		$auction_start_date_by_lots = [];
		$bank_guarantee_by_lots     = [];
		$minimal_steps_by_lots      = [];
		$amounts_by_lots            = [];

		/*
		 * Все id кодов классификаторов по items пренадлижащих тендеру
		 * */
		$allClassifiersCodeIdsByTender = [];

		/*
		Структура данных
			tender  title/description; amount; currency_id; tax_included; contact_name; contact_phone; contact_email; minimal_step by tender
			 lot    title/description; amount; currency_id; tax_included minimal_step; auction_start_date/_end_; status_id; guarantee_amount; guarantee_currency_id
			  item        description; unit_id; quantity; region_name; postal_code; locality; delivery_address; delivery_date_start/end

		*/

		/*
		 * Заголовок и описание могут быть одинаковыми
		 * */
		$title_description                         = [];
		$title_description[ $tender->title ]       = $this->prepareString( $tender->title );
		$title_description[ $tender->description ] = $this->prepareString( $tender->description );
		/*
		 * Основная информацыя по тендеру
		 * */
		$tender_info_all = implode( '<br/>', $this->cleanDescriptions( $title_description ) );


		/*
		 * Проверяем мултилотовость тендера
		 * За основу берем лоты, к ним подключаем items
		 * */
		if ( $tender->multilot ) {

			foreach ( $tender->lots as $lot ) {
				/*
				 * Заголовок и описание могут быть одинаковыми
				 * */
				$title_description                      = [];
				$title_description[ $lot->title ]       = $this->prepareString( $lot->title );
				$title_description[ $lot->description ] = $this->prepareString( $lot->description );

				$lots[ $lot->id ]['description'] = implode( '<br/>', $this->cleanDescriptions( $title_description ) );

				if ( ! is_null( $lot->auction_start_date ) ) {
					$auction_start_date_by_lots[] = strtotime( $lot->auction_start_date );
				}

				if ( ! is_null( $lot->guarantee_amount ) ) {
					$bank_guarantee_by_lots[] = $lot->guarantee_amount . ' ' . $lot->guaranteeCurrency->currency_description;
				}

				/*
				 * Минимальный шаг аукцыона по лотам
				 * */
				if ( ! empty( $lot->minimal_step ) ) {
					$minimal_steps_by_lots[/*$lot->minimal_step*/] = $lot->minimal_step;
				}

				/*
				 * Сумма закупки по лоту
				 * */
				if ( ! empty( $lot->amount ) ) {
					$amounts_by_lots[/*$lot->amount*/] = $lot->amount;
				}
				/* . " " . (empty($lot->currency_id) ? '' : $lot->currency->currency_code) . ($lot->tax_included ? ' з ПДВ' : ' без ПДВ')*/


				foreach ( $lot->items as $item ) {
					$lots[ $lot->id ]['collected_items_info'][] = $this->collectItemInfo( $item );
				}
			}

		} else {
			/*
			 * Для безлотовой закупки
			 * */
			foreach ( $tender->items as $item ) {
				$lots[0]['collected_items_info'][] = $this->collectItemInfo( $item );
			}

		}
		/*
		 * Формирование едтного текста закупки
		 * */
		foreach ( $lots as $lot ) {
			if ( ! empty( $lot['description'] ) ) {
				$tender_info_all .= '<br/><br/>' . $lot['description'];
			}
			$info_grouped_by_classifier = [];
			foreach ( $lot['collected_items_info'] as $items_info ) {
				foreach ( $items_info as $classifier => $item_info ) {
					/*
					 * Групировка всех предметов закупки под один классификатор
					 * */
					$info_grouped_by_classifier[ $classifier ][] = $item_info['description'];
					/*
					 * Адреса доставки, без дублирования
					 * */
					$delivery_addresses[ $item_info['address'] ] = $item_info['address'];

					foreach ( $item_info['classifier_code_ids'] as $code_id ) {
						$allClassifiersCodeIdsByTender[ $code_id ] = $code_id;
					}
				}
			}

			/*
			 * Заполняем предмет закупки информацыей по закупаемым элементам, сгрупированным по классификаторам
			 * */
			foreach ( $info_grouped_by_classifier as $classifier => $item_descriptions ) {
				$tender_info_all .= '<br/>' . $classifier . '<br/>' . implode( '<br/>', $item_descriptions );
			}
		}


		/*
		 * id кодов класификвторов необходимых для фильтрации тендера по коду класификатора
		 * передаем как простая строка, так как на стороне ua-tenders.com код написан на windows-1251 и необходимо переганять данные в кодировку сервака
		 * при этом что бы не дописывать логику по обработке вложенных массивов, просто передаем строку, а после всех преобразований превратим строку в масив
		 *
		 * */
		$schema['all_classifiers_code_ids_by_tender'] = implode( ',', $allClassifiersCodeIdsByTender );

		$schema['tender_place_time'] = implode( '; ', $delivery_addresses );
		$schema['tender_place']      = strip_tags( $tender->organization->getAddress() );

		/*
		 * логика опредидение региона реализована на юа-тендерсе
		 * */
		$schema['tender_region_id'] = 0;

		/*
		 * Банковская гарантия по тендеру и лотам
		 * */
		$schema['tender_supply_all'] =
			( empty( $schema['tender_guarantee_amount'] ) ? '' : $schema['tender_guarantee_amount'] . ' ' . $schema['tender_guarantee_currency'] . '; ' )
			. implode( '; ', $bank_guarantee_by_lots );

		$schema['tender_user_position'] = '';
		$schema['tender_sum_doc']       = 0;

		/*
		 * Описание тендера
		 * */
		$schema['tender_info_all'] = $tender_info_all;

		$schema['tender_sum'] = $tender->amount . " " . $tender->currency->currency_code;
		/*
		 * Разделение по полям суммы закупки и валюты, для простоты поиска на юа-тендерсе и тг.юа
		 * */
		$schema['tender_sum_amount']   = $tender->amount;
		$schema['tender_sum_currency'] = $tender->currency->currency_code;

		$schema['tender_offer_place'] = $url;
		$schema['tender_cond_date']   = $tender->tender_end_date;

		/*
		 * попервой поля приходять пустыми
		 * */
		$schema['tender_open_date'] = ! is_null( $tender->auction_start_date ) ? $tender->auction_start_date : ( count( $auction_start_date_by_lots ) ? date( 'd.m.Y H:i', min( $auction_start_date_by_lots ) ) : null );
		$schema['tender_open_all']  = ! is_null( $tender->auction_start_date ) ? $tender->auction_start_date : ( count( $auction_start_date_by_lots ) ? date( 'd.m.Y H:i', min( $auction_start_date_by_lots ) ) : null );
		/*
		 * контактное лицо
		 * */
		$schema['tender_user_person'] = $tender->organization->contact_name;
		$schema['tender_user_phone']  = $tender->organization->contact_phone;
		$schema['tender_user_mail']   = $tender->organization->contact_email;

		/*
		 * инфа по заказчику
		 * */
		$schema['tender_costumer']                   = $tender->organization->name;
		$schema['tender_costumer_address']           = strip_tags( $tender->organization->getAddress() );
		$schema['tender_costumer_edrpou']            = $tender->organization->identifier;
		$schema['tender_customer_organization_kind'] = $tender->organization->kind_id;

		$schema['TenderVesnik']      = $dateCreated;
		$schema['tender_vesnik_num'] = $dateCreated;
		$schema['_place_serve']      = $url;
		$schema['_place_opening']    = $url;
		$schema['_mode']             = $tender->mode;


		/*
		 *  Дополнительная инфа по тендеру
		 * */
		if ( ! empty( $tender->award->complaint_date_start ) ) {
			$complaintPeriod = ( ! empty( $tender->award->complaint_date_end ) ) ? $tender->award->complaint_date_start . ' - ' . $tender->award->complaint_date_end : $tender->award->complaint_date_start;
		}

		$schema['tender_note'] =
			( ! empty( $tender->minimal_step ) ? "<b>Мiнiмальний крок:</b> {$tender->minimal_step}  {$tender->currency->currency_code}" : '' )
			. ( ! empty( $amounts_by_lots ) ? "<br><b>Сума закупiвлi по лотам:</b> " . implode( '; ', $amounts_by_lots ) : '' )
			. ( ! empty( $minimal_steps_by_lots ) ? "<br><b>Мiнiмальний крок по лотам:</b> " . implode( '; ', $minimal_steps_by_lots ) : '' )
			. ( ! empty( $tender->enquiry_end_date ) ? "<br><b>Закiнчення перiоду уточнення:</b>  {$tender->enquiry_end_date}" : '' )
			. ( ! empty( $complaintPeriod ) ? "<br><b>Початок періоду оскарження:</b>  {$complaintPeriod}" : '' )
			. ( "<br><b>Сума закупiвлi вказана:</b> " );

		if ( $tender->tax_included ) {
			$schema['tender_note'] .= ' з ПДВ';
		} else {
			$schema['tender_note'] .= ' без ПДВ';
		}
		/*
		 * очистка от спецсимволов
		 * */
		$schema['tender_note'] = preg_replace( [ "/[\n\r\t]/", "/\s+/" ], " ", $schema['tender_note'] );
		$schema['cbd_id'] = $tender->cbd_id;
		$schema['cbd_url'] = env('PRZ_API_PUBLIC').'/tenders/'.$tender->cbd_id;

		return $schema;
	}


	/*
	 * матод отвичает за збор и выдачу данных для запрашиваемого фида по протоколу
	 * */
	public function protocol( $id ) {
		/*
		 * Контейнер подготовленного фида
		 * */
		$feed = [
			'tender_number'       => '',
			'tender_public_date'  => '',
			'tender_title'        => '',

			/* заказчик */
			'customer_name'       => '',
			'customer_identifier' => '',
			'customer_address'    => '',

			/* Предмет закупки */
			'tender_info_by_lots' => '',

			'tender_delivery_place' => '',
			'tender_delivery_date'  => '',


			'customer_contact_name'  => '',
			'customer_contact_email' => '',
			'customer_contact_phone' => '',

			'participants_number' => 0,
			'bids'                => [],
			'last_bids'           => [],
			'best_bid'            => [],
			'yamls'               => [],
		];

		$tender = Tender::with( [
			'allBids'   => function ( $q ) {
				$q->with( [ 'organization', 'currency' ] );
			},
			'award'     => function ( $q ) {
				$q->with( [ 'currency', 'bid.organization' ] );
			},
			'documents' => function ( $q ) {
				$q->where( 'title', 'LIKE', '%.yaml' )->first();
			},
			'items'     => function ( $q ) {
				$q->with( [ 'codes', 'unit', 'lot', ] );
			},
		] )
		                ->findOrFail( $id );
		/*
		 * Запонение данными контейнер фида
		 * */
		preg_match( '/UA-(\d\d\d\d-\d\d-\d\d)-(.*)/', $tender->tenderID, $matches );
		$tenderPublicDate = $matches[1];

		$feed['tender_number']      = $tender->tenderID;
		$feed['tender_public_date'] = $tenderPublicDate;
		$feed['tender_title']       = $tender->title . " " . $tender->description;

		$feed['customer_name']       = $tender->organization->name;
		$feed['customer_identifier'] = $tender->organization->identifier;
		$feed['customer_address']    = $tender->organization->getAddress();

		$feed['customer_contact_name']  = $tender->organization->contact_name;
		$feed['customer_contact_email'] = $tender->organization->contact_email;
		$feed['customer_contact_phone'] = $tender->organization->contact_phone;


		/*
		 * Общая схема збора инфы по тендеру
		 * */

		$lots    = [];
		$address = [];
		$codes   = [];

		foreach ( $tender->items as $item ) {
			/*
			 * получение всех кодов класификатора по даному лоту
			 * */
			foreach ( $item->codes as $code ) {
				$codes[] = "{$code->code} - {$code->description}";
			}

			/*
			 * описание лота + коды класификаторов CPV и ДКПП для поля описания тендеру + очистка от спецсимволов
			 * */
			$lots[] = preg_replace( [ "/[\n\r\t]/", "/\s+/" ], " ", "{$codes[0]} ({$codes[1]}): {$item->description} - {$item->quantity} {$item->unit->description}" );

			/*
			 * получение адреса доставки + дата поставки, так как по одному и тому же адресу могут быть поставки в разное время
			 * */
			$delivery_start_end_date = [];
			if ( ! empty( $item->delivery_date_start ) ) {
				$delivery_start_end_date[] = $item->delivery_date_start;
			}
			if ( ! empty( $item->delivery_date_end ) ) {
				$delivery_start_end_date[] = $item->delivery_date_end;
			}

			/*
			 * необработанная строка адреса доставки, в которой может дублироваться только одно название города или страны
			 * */
			$raw_address = strip_tags( $item->getAddress() ) . ( empty( $delivery_start_end_date ) ? '' : ', ' . implode( ' - ', $delivery_start_end_date ) );

			/*
			 * удаление дубликатов
			 * */
			$raw_address_entries               = explode( ', ', $raw_address );
			$_delete_duplicate_address_entries = [];

			foreach ( $raw_address_entries as $address_entry_part ) {
				$_delete_duplicate_address_entries[ $address_entry_part ] = $address_entry_part;
			}

			/*
			 * збор адреса достави после удаления дубликатов в одну строку
			 * */
			$address[] = implode( ', ', $_delete_duplicate_address_entries );

		}

		/*
		 * Просмотр адресов доставки, если все одинаковые то писать только один, без дублирования
		 * */
		$reviewed_address = array();
		foreach ( $address as $one_address ) {
			$reviewed_address[ $one_address ] = $one_address;
		}

		$feed['tender_delivery_date'] = implode( ';<br> ', $reviewed_address );


		$feed['tender_info_by_lots'] = implode( ';<br> ', $lots );
		/*
		 * количество учасников
		 * */
		$feed['participants_number'] = $tender->allBids->count();
		/*
		 * поданные предложения
		 * */
		foreach ( $tender->allBids as $bid ) {
			/*
			 * Информация обучаснике
			 * */
			$feed['bids'][ $bid->cbd_id ] = [
				'name'          => $bid->organization->name,
				'identifier'    => $bid->organization->identifier,
				'address'       => $bid->organization->getAddress(),
				'contact_name'  => $bid->organization->contact_name,
				'contact_phone' => $bid->organization->contact_phone,
				'contact_email' => $bid->organization->contact_email,
			];
			/*
			 * Последнее что  предложил учасник
			 * */
			$feed['last_bids'][ $bid->cbd_id ] = [
				'name'     => $bid->organization->name,
				'amount'   => $bid->amount,
				'currency' => $bid->currency->currency_code,
				'tax'      => $bid->tax_included ? ' з ПДВ ' : ' без ПДВ ',
			];
		}
		/*
		 * Лучшее предложение
		 * */
		$feed['best_bid'] = [

			'name'       => $tender->award->bid->organization->name,
			'identifier' => $tender->award->bid->organization->identifier,
			'address'    => $tender->award->bid->organization->getAddress(),

			'contact_name'  => $tender->award->bid->organization->contact_name,
			'contact_phone' => $tender->award->bid->organization->contact_phone,
			'contact_email' => $tender->award->bid->organization->contact_email,

			'amount'   => $tender->award->amount,
			'currency' => $tender->award->currency->currency_code,
			'tax'      => $tender->award->tax_included ? ' з ПДВ ' : ' без ПДВ ',

			'created_at' => $tender->award->created_at,

			'complaint_dates' => ! empty( $tender->award->complaint_date_end ) ? $tender->award->complaint_date_start . ' - ' . $tender->award->complaint_date_end : $tender->award->complaint_date_start,
		];

		/*
		 * парсинг файла yaml  в котором содержиться последовательность шагов опдачи предложений
		 * */
		foreach ( $tender->documents as $doc ) {
			$yaml = yaml_parse_url( $doc->url );
			/*
			 * возможно файлик пренадлежит совершенно другому тендеру
			 * */
			if ( ! empty( $yaml ) && $yaml['tenderId'] != $tender->tenderID ) {
				continue;
			}

			/*
			 * Если все отлично то заполняем данные проведения аукцыона
			 * */
			/*
			 * Начальные ставки
			 * */
			$initial_bids = [];
			foreach ( $yaml['timeline']['auction_start']['initial_bids'] as $bid ) {
				$initial_bids[] = [
					'name'   => $feed['bids'][ $bid['bidder'] ]['name'],
					'amount' => number_format( $bid['amount'], 2, '.', ' ' ),
					'date'   => Carbon::parse( $bid['date'] )->timezone( 'Europe/Kiev' )->format( 'Y-m-d H:i:s' ),
				];
			}
			/*
			 * Результат проведения торгов
			 * */
			$last_bids = [];
			foreach ( $yaml['timeline']['results']['bids'] as $bid ) {
				$last_bids[] = [
					'name'   => $feed['bids'][ $bid['bidder'] ]['name'],
					'amount' => number_format( $bid['amount'], 2, '.', ' ' ),
					'date'   => Carbon::parse( $bid['time'] )->timezone( 'Europe/Kiev' )->format( 'Y-m-d H:i:s' ),
				];
			}

			/*
			 * Збор инфы по каждому раунду
			 * */
//                1
			$rounds = [];
			foreach ( $yaml['timeline']['round_1'] as $round ) {
				$rounds[1][] = [
					'name'     => $feed['bids'][ $round['bidder'] ]['name'],
					'amount'   => ! empty( $round['amount'] ) ? number_format( $round['amount'], 2, '.', ' ' ) : ' --- ',
					'bid_time' => ! empty( $round['bid_time'] ) ? Carbon::parse( $round['bid_time'] )->timezone( 'Europe/Kiev' )->format( 'Y-m-d H:i:s' ) : ' --- ',
					'time'     => Carbon::parse( $round['time'] )->timezone( 'Europe/Kiev' )->format( 'Y-m-d H:i:s' ),
				];
			}
//                2
			foreach ( $yaml['timeline']['round_2'] as $round ) {
				$rounds[2][] = [
					'name'     => $feed['bids'][ $round['bidder'] ]['name'],
					'amount'   => ! empty( $round['amount'] ) ? number_format( $round['amount'], 2, '.', ' ' ) : ' --- ',
					'bid_time' => ! empty( $round['bid_time'] ) ? Carbon::parse( $round['bid_time'] )->timezone( 'Europe/Kiev' )->format( 'Y-m-d H:i:s' ) : ' --- ',
					'time'     => Carbon::parse( $round['time'] )->timezone( 'Europe/Kiev' )->format( 'Y-m-d H:i:s' ),
				];
			}
//                3
			foreach ( $yaml['timeline']['round_3'] as $round ) {
				$rounds[3][] = [
					'name'     => $feed['bids'][ $round['bidder'] ]['name'],
					'amount'   => ! empty( $round['amount'] ) ? number_format( $round['amount'], 2, '.', ' ' ) : ' --- ',
					'bid_time' => ! empty( $round['bid_time'] ) ? Carbon::parse( $round['bid_time'] )->timezone( 'Europe/Kiev' )->format( 'Y-m-d H:i:s' ) : ' --- ',
					'time'     => Carbon::parse( $round['time'] )->timezone( 'Europe/Kiev' )->format( 'Y-m-d H:i:s' ),
				];
			}
			/*
			 * Результирующее заполнение фида по шагам проведения аукциона
			 * */
			$feed['yamls'][] = [
				'auction_date_start' => Carbon::parse( $yaml['timeline']['auction_start']['time'] )->timezone( 'Europe/Kiev' )->format( 'Y-m-d H:i:s' ),
				'auction_date_end'   => Carbon::parse( $yaml['timeline']['results']['time'] )->timezone( 'Europe/Kiev' )->format( 'Y-m-d H:i:s' ),
				'initial_bids'       => $initial_bids,
				'last_bids'          => $last_bids,
				'rounds'             => $rounds,
			];
		}

		return $feed;
	}


	/*
	 * Вывод данных для фида  по результату проведения торгов
	 * */
	public function result( $id ) {
		/*
		 *  Создаем http клиента для получения недостающих данных по тендеру с ЦБД Prozorro
		 * */
		$сdbHttpClient = new Api( false );
		/*
		 * Laravel collection необходима для удобной манипуляцыи данными
		 * */
		$cancellationsReasonByLotHashId = collect( [] );
		$cancellationsReasonByTender    = collect( [] );

//        352889 для теста , есть как отмененные лоты, так и положительные результаты по лотам
		$feed = [
			/*
			 * для опредиления от кого пришол фид
			 * */
			'source' => 'zakupki',

			'winner'                 => null,
			'res'                    => null,
			'cancel'                 => null,
			/*
			 * Основные данные по результату
			 * */
			'tender_number'          => '',
			/*
			 * cbd_id тендера
			 * */
			'result_number'          => '',
			/*
			 * дата создания тендера
			 * */
			'result_jurnal_date'     => '',
			'jurnal_number_and_data' => '',

			/*
			 *  Полная информацыя по закупке
			 * */
			'result_order_full'      => '',


		];

		/*
		 * Получаем тендер по котрому будем делать  рузультат
		 * */
		$tender = Tender::with( [
			/*
			 *  Берем только те записи что имеют лоты с указанным статусом
			 * */
			'items'  => function ( $q ) {
				$q->with( 'lot.cancel' )->whereHas( 'lot', function ( $q ) {
					$q->whereIn( 'status', [ 'cancelled', 'unsuccessful' ] );
				} );
			},
			/*
			 *  Выбираем только активных кандидатов на победу
			 * */
			'awards' => function ( $q ) {
				$q->with( [ 'bid.organization', 'contract' ] )
				  ->whereStatus( 'active' );
			},

		] )->find( $id );

		/*
		 * Если тендера нет отправляем маленький ответ
		 * */
		if ( ! $tender ) {
			return [ 'id' => $id, 'error' => 'Not exists' ];
		}

		/*
		 *  Подтягиваем данные от ЦБД  на тот случай если в базе чего то не хватает
		 * */
		$ProzorroFeed = $сdbHttpClient->get( $tender->cbd_id, '/cancellations' );

		/*
		 * $ProzorroFeed['data']['cancellations'][]['relatedLot'] hash_id лота к которому относиться отмена
		 * $ProzorroFeed['data']['cancellations'][]['reason'] причина отмены лота
		 * $ProzorroFeed['data']['cancellations'][]['cancellationOf'] может быть tender или lot
		 * */

		if ( ! empty( $ProzorroFeed['data'] ) ) {
			/*
			 * Laravel collection необходима для удобной манипуляцыи данными
			 * */
			$ProzorroDataCollection = collect( $ProzorroFeed['data'] );
			if ( $ProzorroDataCollection->count() ) {

				/*
				 * Групируем данные отмены по принадлежности к элементу
				 * */
				$GroupedCancellationsCollection =
					$ProzorroDataCollection->groupBy( 'cancellationOf' );

				/*
				 *  Вытаскиваем все отмены по лотам
				 * */
				if ( $GroupedCancellationsCollection->has( 'lot' ) ) {
					$cancellationsReasonByLotHashId = $GroupedCancellationsCollection->get( 'lot' )->keyBy( 'relatedLot' );
				}
				/*
				 *  Вытаскиваем отмену по тендеру
				 * */
				if ( $GroupedCancellationsCollection->has( 'tender' ) ) {
					$cancellationsReasonByTender = $GroupedCancellationsCollection->get( 'tender' )->keyBy( 'cancellationOf' );
				}

			}
		}


		preg_match( '/UA-(\d\d\d\d-\d\d-\d\d)-(.*)/', $tender->tenderID, $matches );
		$TenderCreatedDate = $matches[1];

		/*
		 *  Общая инфа по результату
		 * */
		$feed['tender_number'] = $tender->tenderID;

		$feed['result_number']          = $tender->cbd_id;
		$feed['result_jurnal_date']     = Carbon::parse( $TenderCreatedDate )->format( 'd.m.Y' );
		$feed['jurnal_number_and_data'] = Carbon::parse( $TenderCreatedDate )->format( 'd.m.Y' );


		/*
		 * Если тендер был отменен или не состоялся, проверяем есть ли соответствующие записи
		 * полученные по заданому запросу
		 * */
		if (
			in_array( $tender->status, [ 'cancelled', 'unsuccessful' ] )
			&& ! $tender->awards->count()
			&& ! $tender->items->count()
		) {

			/*
			 * Отмены
			 * */
			$reason = '';
			if ( $tender->status == 'unsuccessful' ) {
				$reason = 'По тендеру не подано пропозицій';
			} elseif ( $tender->status == 'cancelled' && $tender->cancel ) {
				$reason = $tender->cancel->reason;
			} elseif ( $cancellationsReasonByTender->has( 'tender' ) ) {
				$reason = $this->prepareString( $cancellationsReasonByTender->get( 'tender' )['reason'] );
			}

			$feed['cancel'][] = [
				/*
				 * Вместо лота необходимо писать данные по тендеру
				 * */
				'cancel_lot_info' => $this->prepareString( "{$tender->title}<br>{$tender->description}" ),
				'cancel_date'     => Carbon::parse( $tender->date_modified )->format( 'd.m.Y' ),
				'cancel_reason'   => $reason,
				/*
				 * Отсуствующая информацыя
				 * */
				'cancel_lot'      => null,
				'cancel_other'    => null,
			];

			$feed['result_order_full'] .= $tender->title . ':<br>' . $tender->description . '<br>';
			/*
			* Дополняем информацыю описанием элементов
			* */
			$items        = $tender->items()->get();
			$descriptions = $items->map( function ( $item ) {
				return $item->description;
			} );

			$feed['result_order_full'] .= $descriptions->implode( ';<br>' );

			return $feed;
		}

		/*
		 *  Формируем фид, каждая часть отвечает за свое предстаавление информацыи
		 *  awards  представляет информацыю аукцыона по лотом или тендеру завершенного успешно
		 * */
		foreach ( $tender->awards as $award ) {
			/*
			 * Победители
			 * */
			$feed['winner'][ $award->bid->organization->id ] = [
				'winner_contact' => $award->bid->organization->name,
				'winner_edrpou'  => $award->bid->organization->identifier,
				'winner_address' => $award->bid->organization->getAddress(),
				'winner_phone'   => $award->bid->organization->contact_phone,
			];

			/*
			 * Необходимо как можно больше данных
			 * лот если присуствует, придмет закупки (item) название, количество
			 * */
			$descriptions = [];
			if ( $award->bid->bidable->type == 'item' ) {

				if ( ! empty( $award->bid->bidable->lot->title ) ) {
					$key_string                  = mb_strtoupper( $award->bid->bidable->lot->title, 'UTF-8' );
					$descriptions[ $key_string ] = $this->prepareString( $key_string );
				}
				if ( ! empty( $award->bid->bidable->lot->description ) ) {
					$key_string                  = mb_strtoupper( $award->bid->bidable->lot->description, 'UTF-8' );
					$descriptions[ $key_string ] = $this->prepareString( $key_string );
				}

				$descriptions[] =
					$this->prepareString( $award->bid->bidable->description . ' - ' . $award->bid->bidable->quantity . ' ' . $award->bid->bidable->unit->description );


			} else {
				/*
				 * Предполагаеться что это лот
				 * */
				if ( ! empty( $award->bid->bidable->title ) ) {
					$key_string                  = mb_strtoupper( $award->bid->bidable->title, 'UTF-8' );
					$descriptions[ $key_string ] = $this->prepareString( $key_string );
				}
				if ( ! empty( $award->bid->bidable->description ) ) {
					$key_string                  = mb_strtoupper( $award->bid->bidable->description, 'UTF-8' );
					$descriptions[ $key_string ] = $this->prepareString( $key_string );
				}

				foreach ( $award->bid->bidable->items as $item ) {
					$descriptions[] = $this->prepareString( $item->description . ' - ' . $item->quantity . ' ' . $item->unit->description );
				}
			}

			/*
						if (count($descriptions) > 1)
							array_unshift($descriptions, 'Лот.');
			*/
			$winner_lot_info = implode( '<br/>', $descriptions );

			/*
			 *  Результаты победителей по лотам
			 * */
			$feed['res'][] = [
				'winner'               => $award->bid->organization->name,
				'winner_lot_info'      => $winner_lot_info,

				/* дата тендера award_end_date это дата до которой заказчик должен определиться с победителем, или отменить торги с укзанной присиной*/
				'winner_accept_data'   => Carbon::parse( $award->date )->format( 'd.m.Y' ),
				'winner_contract_data' => Carbon::parse( $award->contract->date )->format( 'd.m.Y' ),

				'sum'               => $award->amount,
				'rate_pdv'          => ( $award->tax_included ? 'з' : 'без' ) . ' ПДВ',
				/*
				 *  невостребованные поля и отсуствующая информацыя
				 * */
				'winner_other_info' => null,
				'winner_lot_number' => null,
			];

			$feed['result_order_full'] .= "{$winner_lot_info}<br><br>";
		}


		foreach ( $tender->items as $item ) {
			/*
			 * Отмены
			 * */
			$reason = '';
			if ( $item->lot->status == 'unsuccessful' ) {
				$reason = 'Тендер не відбувся';
			} elseif ( $item->lot->status == 'cancelled' && $item->lot->cancel ) {
				$reason = $item->lot->cancel->reason;
			} elseif ( $cancellationsReasonByLotHashId->has( $item->lot->cbd_id ) ) {
				$reason = $this->prepareString( $cancellationsReasonByLotHashId->get( $item->lot->cbd_id )['reason'] );
			}


			$descriptions = [];

			if ( ! empty( $item->lot->title ) ) {
				$key_string                  = mb_strtoupper( $item->lot->title, 'UTF-8' );
				$descriptions[ $key_string ] = $this->prepareString( $key_string );
			}
			if ( ! empty( $item->lot->description ) ) {
				$key_string                  = mb_strtoupper( $item->lot->description, 'UTF-8' );
				$descriptions[ $key_string ] = $this->prepareString( $key_string );
			}

			$descriptions[] =
				$this->prepareString( $item->description . ' - ' . $item->quantity . ' ' . $item->unit->description );
			/*
						if (count($descriptions) > 1)
							array_unshift($descriptions, 'Лот.');
			*/
			$cancel_lot_info = implode( '<br/>', $descriptions );

			$feed['cancel'][] = [
				/*
				 * Вместо лота необходимо писать
				 * */
				'cancel_lot_info' => $cancel_lot_info,

				'cancel_date'   => Carbon::parse( $item->lot->date )->format( 'd.m.Y' ),
				'cancel_reason' => $reason,
				/*
				 * Отсуствующая информацыя
				 * */
				'cancel_lot'    => null,
				'cancel_other'  => null,
			];

			$feed['result_order_full'] .= "{$cancel_lot_info}<br><br>";
		}


		return $feed;
	}


	/*
	 * Формирование результата по лотам
	 * */
	public function result_by_lot( $lotId ) {

		/*
		 *  Создаем http клиента для получения недостающих данных по тендеру с ЦБД Prozorro
		 * */
		$сdbHttpClient = new Api( false );
		/*
		 * Laravel collection необходима для удобной манипуляцыи данными
		 * */
		$cancellationsReasonByLotHashId = collect( [] );
		$cancellationsReasonByTender    = collect( [] );

//        352889 для теста , есть как отмененные лоты, так и положительные результаты по лотам
		$feed = [
			/*
			 * для опредиления от кого пришол фид
			 * */
			'source' => 'zakupki',

			'winner'                 => null,
			'res'                    => null,
			'cancel'                 => null,
			/*
			 * Основные данные по результату
			 * */
			'tender_number'          => '',

			/*
			 * содержит cbd_id лота по которому создается результат
			 * */
			'result_number'          => '',
			/*
			 * дата тендера с его уникально id формат UA-2016-01-01......
			 * */
			'result_jurnal_date'     => '',
			'jurnal_number_and_data' => '',

			/*
			 *  Полная информацыя по закупке
			 * */
			'result_order_full'      => '',


		];

		/*
		 * Получаем лот по которому необходимо сформировать результат
		 * */
		$lot = Lot::with( [

			'tender',
			'items.codes',
			'items.unit',

			'cancel',

			'bids.currency',
			'bids.organization',

			'bids.award',
			'bids.award.contract',
			'bids.award.currency',

			'bids' => function ( $q ) {
				$q->has( 'award' );
			},

		] )->find( $lotId );

		/*
		 * Возможно ошибка и передан неверный id
		 * */
		if ( empty( $lot ) ) {
			return [ 'id' => $lotId, 'error' => 'Not exists' ];
		}

		/*
		 *  Подтягиваем данные от ЦБД  на тот случай если в базе чего то не хватает
		 *  на данный момент, необходимо подтягивать причину отмены
		 * */
		$ProzorroFeed = $сdbHttpClient->get( $lot->tender->cbd_id, '/cancellations' );

		/*
		 * $ProzorroFeed['data']['cancellations'][]['relatedLot'] hash_id лота к которому относиться отмена
		 * $ProzorroFeed['data']['cancellations'][]['reason'] причина отмены лота
		 * $ProzorroFeed['data']['cancellations'][]['cancellationOf'] может быть tender или lot
		 * */

		if ( ! empty( $ProzorroFeed['data'] ) ) {
			/*
			 * Laravel collection необходима для удобной манипуляцыи данными
			 * */
			$ProzorroDataCollection = collect( $ProzorroFeed['data'] );

			if ( $ProzorroDataCollection->count() ) {

				/*
				 * Групируем данные отмены по принадлежности к элементу
				 * */
				$GroupedCancellationsCollection =
					$ProzorroDataCollection->groupBy( 'cancellationOf' );
				/*
				 * результатом будет массив
				 * [
				 *   lot    => [ ],
				 *   tender => [ ],
				 * ]
				 * */

				/*
				 *  Вытаскиваем все отмены по лотам
				 * */
				if ( $GroupedCancellationsCollection->has( 'lot' ) ) {
					$cancellationsReasonByLotHashId = $GroupedCancellationsCollection->get( 'lot' )->keyBy( 'relatedLot' );
					/*
					 * результатом выполнения будет массив ключами которого будет cbd_id лота
					 * */
				}
				/*
				 *  Вытаскиваем отмену по тендеру, пока не востребованы
				 * */
				if ( $GroupedCancellationsCollection->has( 'tender' ) ) {
					$cancellationsReasonByTender = $GroupedCancellationsCollection->get( 'tender' )->keyBy( 'cancellationOf' );
					/*
					 * простой массив содержащий причину отмены по тендеру, так же дополнительные данные
					 * */
				}

			}
		}

		preg_match( '/UA-(\d\d\d\d-\d\d-\d\d)-(.*)/', $lot->tender->tenderID, $matches );
		$TenderCreatedDate = $matches[1];
		/*
		 *  Общая инфа по результату
		 * */
		$feed['tender_number'] = $lot->tender->tenderID;
		/*
		 * уникальность результата по лоту достигаем при помощи cbd_id лота
		 * */
		$feed['result_number'] = $lot->cbd_id;
		/*
		 * Выбираем дату создания тендера по его уникальному id
		 * */
		$feed['result_jurnal_date']     = Carbon::parse( $TenderCreatedDate )->format( 'd.m.Y' );
		$feed['jurnal_number_and_data'] = Carbon::parse( $TenderCreatedDate )->format( 'd.m.Y' );


		/*
		 * Описание Лота
		 * */
		$descriptions = [];


		if ( ! empty( $lot->title ) ) {
			$key_string                  = mb_strtoupper( $lot->title, 'UTF-8' );
			$descriptions[ $key_string ] = $this->prepareString( $key_string );
		}
		if ( ! empty( $lot->description ) ) {
			$key_string                  = mb_strtoupper( $lot->description, 'UTF-8' );
			$descriptions[ $key_string ] = $this->prepareString( $key_string );
		}

		/*
		 * Получение информации обо всех items  в лоте
		 * */
		foreach ( $lot->items as $item ) {
			$descriptions[] = $this->prepareString( $item->description . ' - ' . $item->quantity . ' ' . $item->unit->description );
		}
		/*
		 * Если лот в статусе complete
		 * Фоормируем данные по победителю
		 * */
		if ( $lot->status == 'complete' ) {

			/*
			 * Проход по всех предложениях по лоту
			 * должен быть только одно активное предложение
			 * */
			foreach ( $lot->bids as $bid ) {
				/*
				 * Победители
				 * */
				$feed['winner'][ $bid->organization->id ] = [
					'winner_contact' => $bid->organization->name,
					'winner_edrpou'  => $bid->organization->identifier,
					'winner_address' => $bid->organization->getAddress(),
					'winner_phone'   => $bid->organization->contact_phone,
				];


				$winner_lot_info = implode( '<br/>', $descriptions );

				/*
				 *  Результаты победителей по лоту
				 * */
				$feed['res'][] = [
					'winner'             => $bid->organization->name,
					'winner_lot_info'    => $winner_lot_info,

					/* дата тендера award_end_date это дата до которой заказчик должен определиться с победителем, или отменить торги с укзанной причиной*/

					/* ждать изминения, добавят дату подписания намерения */
					'winner_accept_data' => Carbon::parse( $bid->award->date )->format( 'd.m.Y' ),

					'winner_contract_data' => Carbon::parse( $bid->award->contract->date )->format( 'd.m.Y' ),

					'sum'               => $bid->award->amount,
					'rate_pdv'          => ( $bid->award->tax_included ? 'з' : 'без' ) . ' ПДВ',
					/*
					 *  невостребованные поля и отсуствующая информацыя
					 * */
					'winner_other_info' => null,
					'winner_lot_number' => null,
				];

				$feed['result_order_full'] .= "{$winner_lot_info}<br><br>";
			}
		} //COMPLETE


		/*
		 * Если лот был отменен или не состоялся, проверяем есть ли соответствующие записи
		 * полученные по заданому запросу
		 * */
		if ( in_array( $lot->status, [ 'cancelled', 'unsuccessful' ] ) ) {

			/*
			 * Причина отмены
			 * */
			$reason = '';
			if ( $lot->status == 'unsuccessful' ) {
				$reason = 'По лоту тендер не відбувся';
			} elseif ( $lot->status == 'cancelled' && $lot->cancel ) {
				$reason = $lot->cancel->reason;
			} elseif ( $cancellationsReasonByLotHashId->has( $lot->cbd_id ) ) {
				$reason = $this->prepareString( $cancellationsReasonByLotHashId->get( $lot->cbd_id )['reason'] );
			}


			$cancel_lot_info = implode( '<br/>', $descriptions );

			$feed['cancel'][] = [
				/*
				 * Вместо лота необходимо писать
				 * */
				'cancel_lot_info' => $cancel_lot_info,

				'cancel_date'   => Carbon::parse( $lot->date )->format( 'd.m.Y' ),
				'cancel_reason' => $reason,
				/*
				 * Отсуствующая информацыя
				 * */
				'cancel_lot'    => null,
				'cancel_other'  => null,
			];

			$feed['result_order_full'] .= "{$cancel_lot_info}<br><br>";

		} // CANCELLED/UNSUCCESSFUL


		return $feed;

	}


	/*
	 *  Собирает данные по указанной дате, по умолчанию берет тикущие сутки
	 *  Дату принимает в виде timestamp
	 * */
	public function statistics( $timestamp ) {
		/*
		 * Собрать количество добавленных тендеров по указанной дате
		 * */
		if ( $timestamp && is_numeric( $timestamp ) ) {
			$date = date( 'Y-m-d H:i:s', $timestamp );
		} else {
			$date = date( 'Y-m-d 00:00:00' );
		}
	}

	protected function prepareString( $string ) {
		$string = preg_replace( [ "/[\n\r\t]/", "/\s+/" ], " ", $string );
		$string = trim( $string );
		$string = strip_tags( $string, '<br/><br>' );

		return $string;
	}

	protected function collectItemInfo( $item ) {
		$collectedInfo = [];

		$codes                   = [];
		$delivery_start_end_date = [];
		/*
		 * Все id кодов классификаторов по items пренадлижащих тендеру
		 * */
		$classifier_code_ids = [];

		/*
		* получение всех кодов класификатора по даному лоту
		* */
		foreach ( $item->codes as $code ) {
			$codes[]               = $code->code . ' - ' . $code->description;
			$classifier_code_ids[] = $code->id;
		}
		$main_key = implode( '; ', $codes );
		/*
		 * Основа, групировать все item по коду класификатора, для избижания дублирования в выводе
		 * так же групировать по пренадлежности к лоту
		 * описание лота + коды класификаторов CPV и ДКПП для поля описания тендеру + очистка от спецсимволов
		 * */
		$collectedInfo[ $main_key ]['classifier_code_ids'] = $classifier_code_ids;
		$collectedInfo[ $main_key ]['description']         = $this->prepareString( "{$item->description} - {$item->quantity} {$item->unit->description}" );
		/*
		 * получение адреса доставки + дата поставки, так как по одному и тому же адресу могут быть поставки в разное время
		 * */
		if ( ! empty( $item->delivery_date_start ) ) {
			$delivery_start_end_date[] = $item->delivery_date_start;
		}
		if ( ! empty( $item->delivery_date_end ) ) {
			$delivery_start_end_date[] = $item->delivery_date_end;
		}

		/*
		 * необработанная строка адреса доставки, в которой может дублироваться только одно название города или страны
		 * */
		$raw_address = strip_tags( $item->getAddress() ) . ( empty( $delivery_start_end_date ) ? '' : ', ' . implode( ' - ', $delivery_start_end_date ) );

		/*
		 * удаление дубликатов
		 * */
		$raw_address_entries               = explode( ', ', $raw_address );
		$_delete_duplicate_address_entries = [];

		foreach ( $raw_address_entries as $address_entry_part ) {
			$_delete_duplicate_address_entries[ $address_entry_part ] = $address_entry_part;
		}

		/*
		 * збор адреса достави после удаления дубликатов в одну строку
		 * */
		$collectedInfo[ $main_key ]['address'] = implode( ', ', $_delete_duplicate_address_entries );


		return $collectedInfo;

	}


	protected function cleanDescriptions( $descriptions = array() ) {
		foreach ( $descriptions as $key => $description ) {
			if ( is_null( $description ) || trim( $description ) === '' ) {
				unset( $descriptions[ $key ] );
			}
		}

		return $descriptions;
	}
}
