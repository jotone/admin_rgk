function oneHeight() {
    var rows = $('.sTable.Add-on_price tbody tr').length;


    for(var i =0; i<rows; i++){
        var height = 0;
        $('.sTable.Add-on_price tbody tr').eq(i).find('td').each(function () {
            if($(this).outerHeight() > height){
                height = $(this).outerHeight();
            }
        });
        $('.table-row').eq(i).find('table-td').each(function () {
            if($(this).outerHeight()>height){
                height = $(this).outerHeight();
            }
        });
        $('.sTable.Add-on_price tbody tr').eq(i).find('td').each(function () {
            $(this).attr('style','height:'+height);
        });
        $('.table-row').eq(i).find('.table-td').each(function () {
            $(this).css('height',height);
        });
    }
}
function hoverTableRow() {
    $('.sTable.Add-on_price tbody tr').hover(function () {
        var ind = $(this).index();
        
        $('.table-row').eq(ind).addClass('hover');
    },function () {
        var ind = $(this).index();

        $('.table-row').eq(ind).removeClass('hover');
    });
    $('.table-row').hover(function () {
        var ind = $(this).index()-1;

        $('.sTable.Add-on_price tbody tr').eq(ind).addClass('hover');
    },function () {
        var ind = $(this).index()-1;

        $('.sTable.Add-on_price tbody tr').eq(ind).removeClass('hover');
    });
}
$(document).ready(function () {
    oneHeight();
    hoverTableRow();
});