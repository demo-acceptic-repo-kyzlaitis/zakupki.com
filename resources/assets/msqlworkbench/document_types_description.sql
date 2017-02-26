USE homestead;

INSERT INTO
  document_types (id,namespace, document_type, lang_ua)
VALUES
  (1,'tender', 'notice', 'Повідомлення про закупівлю'),
  (2,'tender', 'biddingDocuments', 'Документи закупівлі'),
  (3,'tender', 'technicalSpecifications', 'Технічні специфікації'),
  (4,'tender', 'evaluationCriteria', 'Критерії оцінки'),
  (5,'tender', 'clarifications', 'Пояснення до питань заданих учасниками'),
  (6,'tender', 'eligibilityCriteria', 'Критерії прийнятності'),
  (7,'tender', 'shortlistedFirms', 'Фірми у короткому списку'),
  (8,'tender', 'riskProvisions', 'Положення для управління ризиками та зобов’язаннями'),
  (9,'tender', 'billOfQuantity', 'Кошторис'),
  (10,'tender', 'bidders', 'Інформація про учасників'),
  (11,'tender', 'conflictOfInterest', 'Виявлені конфлікти інтересів'),
  (12,'tender', 'debarments', 'Недопущення до закупівлі'),
  (13,'award', 'notice', 'Повідомлення про рішення'),
  (14,'award', 'evaluationReports', 'Звіт про оцінку'),
  (15,'award', 'winningBid', 'Пропозиція, що перемогла'),
  (16,'award', 'complaints', 'Скарги та рішення'),
  (17,'contract', 'notice', 'Повідомлення про договір'),
  (18,'contract', 'contractSigned', 'Підписаний договір'),
  (19,'contract', 'contractArrangements', 'Заходи для припинення договору'),
  (20,'contract', 'contractSchedule', 'Розклад та етапи'),
  (21,'contract', 'contractAnnexe', 'Додатки до договору'),
  (22,'contract', 'contractGuarantees', 'Гарантії'),
  (23,'contract', 'subContract', 'Субпідряд');