USE homestead;

INSERT INTO
  statuses (id, namespace, status, description)
VALUES
  (1, 'bid', 'registration', 'реєстрація'),
  (2, 'bid', 'validBid', 'дійсна пропозиція'),
  (3, 'bid', 'invalidBid', 'недійсна пропозиція'),
  (4, 'award', 'pending', 'переможець розглядається кваліфікаційною комісією'),
  (5, 'award', 'unsuccessful', 'кваліфікаційна комісія відмовила переможцю'),
  (6, 'award', 'active', 'закупівлю виграв учасник з пропозицією bid_id'),
  (7, 'award', 'cancelled', 'орган, що розглядає скарги, відмінив результати закупівлі'),
  (8, 'complaint', 'pending', 'не вирішено, ще обробляється'),
  (9, 'complaint', 'invalid', 'недійсно'),
  (10, 'complaint', 'declined', 'відхилено'),
  (11, 'complaint', 'resolved', 'вирішено'),
  (12, 'contract', 'pending', 'цей договір запропоновано, але він ще не діє. Можливо очікується його підписання.'),
  (13, 'contract', 'active', 'цей договір підписаний всіма учасниками, і зараз діє на законних підставах.'),
  (14, 'contract', 'cancelled', 'цей договір було скасовано до підписання.'),
  (15, 'contract', 'terminated', 'цей договір був підписаний та діяв, але вже завершився. Це може бути пов’язано з виконанням договору, або з достроковим припиненням через якусь незавершеність.'),
  (16, 'cancellation', 'pending', 'Стандартно. Запит оформляється.'),
  (17, 'cancellation', 'active', 'Скасування активоване.'),
  (18, 'tender', 'active.enquiries', 'Період уточнень (уточнення)'),
  (19, 'tender', 'active.tendering', 'Очікування пропозицій (пропозиції)'),
  (20, 'tender', 'active.auction', 'Період аукціону (аукціон)'),
  (21, 'tender', 'active.qualification', 'Кваліфікація переможця (кваліфікація)'),
  (22, 'tender', 'active.awarded', 'Пропозиції розглянуто (розглянуто)'),
  (23, 'tender', 'unsuccessful', 'Закупівля не відбулась (не відбулась)'),
  (24, 'tender', 'complete', 'Завершена закупівля (завершена)'),
  (25, 'tender', 'cancelled', 'Відмінена закупівля (відмінена)');