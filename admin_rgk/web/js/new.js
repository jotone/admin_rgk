function oneHeight() {
    $('.table-new-one .wdth').each(function () {
        var ind = $(this).index();
        var hgt = 0;
        for(var i =0; i < $(this).find('.table-td').length; i++ ){

            if($(this).find('.table-td').eq(i).outerHeight() >hgt){hgt = $(this).find('.table-td').eq(i).outerHeight();}

        }
        for(var j =0; j < $('.table-new-two .wdth').eq(ind).find('.table-td').length; j++ ){
            if($('.table-new-two .wdth').eq(ind).find('.table-td').eq(j).outerHeight() > hgt){hgt = $('.table-new-two .wdth').eq(ind).find('.table-td').eq(j).outerHeight();}

        }


        $(this).find('.table-td').each(function () {
            $(this).css('height',hgt);
        });
        $('.table-new-two .wdth').eq(ind).find('.table-td').each(function () {
            $(this).css('height',hgt);
        });

    });
    
}
function oneWidth() {
    var wdth = [];
    $('.table-new-two .wdth').each(function () {
        for (var i = 0; i<$(this).find('.table-td').length; i++){
           if (typeof wdth[i] == 'undefined' || $(this).find('.table-td').eq(i).outerWidth() > wdth[i]){wdth[i] = $(this).find('.table-td').eq(i).outerWidth();}
        }

    });
    $('.table-new-two .wdth').each(function () {
        for (var i = 0; i<$(this).find('.table-td').length; i++){
            $(this).find('.table-td').eq(i).css('width',wdth[i]);
        }
    });
    var sum = 0;
    for (var i =0 ; i<wdth.length; i++){
        sum = sum+wdth[i]+2;
    }

    $('.table-new-two').css('width',sum);

    if(sum<$('.new-table-price').outerWidth()){
        $('.new-table-price').css('overflow','auto');
    }


}
function hoverTableRow() {
    $('.table-new-two .table-row').hover(function () {
        var ind = $(this).index()-1;
        $('.table-new-one .table-row').eq(ind).addClass('hover');
    },function () {
        var ind = $(this).index()-1;
        $('.table-new-one .table-row').eq(ind).removeClass('hover');
    });
    $('.table-new-one .table-row').hover(function () {
        var ind = $(this).index()-1;
        $('.table-new-two .table-row').eq(ind).addClass('hover');
    },function () {
        var ind = $(this).index()-1;
        $('.table-new-two .table-row').eq(ind).removeClass('hover');
    });
}
$(document).ready(function () {

    hoverTableRow();
    oneWidth();
    setTimeout(function () {
        oneHeight();
    },100);
});