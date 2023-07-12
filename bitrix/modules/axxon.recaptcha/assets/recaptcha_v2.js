/**
 * Инициализация капчи по id
 * @param id
 */
const initRecaptcha = function(id) {
    grecaptcha.render(id, {
        'sitekey' : window['reCaptchaKey']
    });
};

/**
 * Перебираем все элементы и инициализируем капчу
 */
function onloadCallback() {
    jQuery('[data-captcha]').each(function (index, element) {
        initRecaptcha(element.id);
    });
}