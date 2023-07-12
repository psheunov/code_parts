/**
 * Инициализация капчи по id
 * @param id
 */
const initRecaptcha = function(id) {
    grecaptcha.execute(window['reCaptchaKey'], {action: 'login'})
        .then(function(token) {
            document.getElementById(id).value=token;
        });
};

/**
 * Перебираем все элементы и инициализируем капчу
 */
grecaptcha.ready(function() {
    jQuery('[data-captcha]').each(function (index, element) {
        initRecaptcha(element.id);
    });
});