$( document ).ready(function() {
    $.sessionTimeout({
        title: 'Завершення робочої сесії',
        message: 'Натисніть Завершити, щоб завершити сесію і вийти з особистого кабінету або Продовжити, якщо хочете продовжити роботу в особистому кабінеті',
        countdownBar: true,
        countdownMessage: 'Ваша робоча сесія завершиться через {timer}',
        logoutButton: 'Завершити',
        keepAliveButton: 'Продовжити',
        ignoreUserActivity: true, //не будет срабатывать при движении мышки
        warnAfter: 1200000, //1200000, // показать окно завершения сесси через 20
        redirAfter: 1800000, //600000, // 10 минут
        countdownSmart: true,
        logoutUrl: '/logout',
        redirUrl: '/logout',
        keepAliveUrl: '/keep-alive',
        ajaxData: { _token: $("head > meta[name='csrf-token']").attr('content') },
        keepAlive: true
    });
});