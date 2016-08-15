/*table*/
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
    function oneWidth() { // one width for td
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


        var sum = 0;   //total table width for table-row:hover corect work
        for (var i =0 ; i<wdth.length; i++){
            sum = sum+wdth[i]+2;
        }
        $('.table-new-two').css('width',sum);

        if(sum<$('.new-table-price').outerWidth()){
            $('.new-table-price').css('overflow','auto');
        }


    }
    function hoverTableRow() {//one hover for other tables row
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
/*END table*/
/*smart search*/
    function clickOnPlus() {
        $(document).on('click','.icon_lplus', function () {
            $(this).toggleClass('active');
            if($(this).hasClass('active')){
                $(this).parent().next().addClass('active');
                $(this).parent().next().find('>li').addClass('active');
            }else{
                $(this).parent().next().removeClass('active');
                $(this).parent().next().find('>li').removeClass('active');
            }

        })
    }
    function blockContextMenu(){ //block context menu and call need function
        var el = document.querySelectorAll('.catalogList a');
        for(var i = 0; i<el.length; i++){
            el[i].addEventListener('contextmenu', function(event) {
                event = event || window.event;
                event.preventDefault ? event.preventDefault() : event.returnValue = false;

                var pos = $('.catalogList-wrap').offset(),
                    elem_left = pos.left,
                    elem_top = pos.top,
                    Xinner = event.pageX - elem_left,
                    Yinner = event.pageY - elem_top;
                $('.catalogList ul a').removeClass('active');
                $(this).addClass('active');
                $('.modalWindow').css({'display':'block','left':Xinner,'top':Yinner});

                return false;
            }, false);
        }
    }
    function search() {
        chekForActive();
        plusMinusImg();
        var input = $('#smartSearch');
        input.keypress( function () {
            setTimeout(function () {
                var val = input.val();
                var reg=new RegExp(val,"i");
                var flag = false;
               resetView();
                $('.catalogList ul a').each(function () {
                    var str = $(this).text();
                    var result=reg.test(str);
                    if(result == true){

                            $(this).parents('ul').addClass('active');
                            $(this).parents('li').addClass('active');

                        flag = true;
                    }

                });
                plusMinusImg();
                
                if(flag == false){resetView();}



            },100);
        });
        function resetView(){
            $('.catalogList ul>li ul').removeClass('active');
            $('.catalogList ul li ').removeClass('active');

        }
        function plusMinusImg(){
            $('.icon_lplus').each(
                function () {
                    if($(this).parent().next().hasClass('active')){$(this).addClass('active');}
                }
            );

        }
        function chekForActive() {
            $('.catalogList ul a').each(function () {
               if ($(this).hasClass('active')){
                   $(this).parents('ul').addClass('active');
                   $(this).parents('li').addClass('active');
                   $(this).parents('ul').find('>li').addClass('active');
               }
            });
        }
    }


    function menuMoveItem(popupContent){
        $.fancybox.open({
            content: '<div class="alertPopup">'+popupContent+'</div>',
            padding:0,
            fitToView:false,
            autoSize:true,
            wrapCSS: 'classWrap'
        });
    }

    
/*END smart search*/
$(document).ready(function () {

    hoverTableRow();
    oneWidth();
    setTimeout(function () {
        oneHeight();
    },100);
    clickOnPlus();
blockContextMenu();
    search();



});