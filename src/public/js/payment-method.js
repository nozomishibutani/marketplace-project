$(function () {

    const select = $('.payment-method');
    const display = $('.selected-payment');

    function update() {
        const text = select.find('option:selected').text();
        display.text(text);
    }

    // 初期表示
    update();
    // 変更時
    select.on('change', update);
});
