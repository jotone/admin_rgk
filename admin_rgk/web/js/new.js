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
                var itemId = $(this).attr('data-id');
                var parentId = $(this).attr('data-parentid');
                var title = $(this).text();
                var pos = $('.catalogList-wrap').offset(),
                    elem_left = pos.left,
                    elem_top = pos.top,
                    Xinner = event.pageX - elem_left,
                    Yinner = event.pageY - elem_top;
                $('.catalogList ul a').removeClass('active');
                $(this).addClass('active');
                $('.modalWindow').css({'display':'block','left':Xinner,'top':Yinner}).attr('data-id',itemId);
                createItem(itemId);
                createGroup(itemId);
                editItem($(this), itemId, parentId);
                moveItem(itemId, title);
                deleteItem(itemId);

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
    //context menu on smart search
        //functional move Item
            function moveItem(id, title) {
                $(document).on('click', '.moveItem', function (e) {
                    e.preventDefault();
                    $.ajax({
                        type:'get',
                        url:'/app_dev.php/sectionList',
                        data:{id:id},
                        beforeSend:function () {
                          showPreloader();
                        },
                        success:function (data) {
                            hidePreloader();
                            var content = generatePopUpContent(data, id);
                            $.fancybox.open({
                                content: content,
                                padding:0,
                                fitToView:false,
                                autoSize:true,
                                wrapCSS: 'classWrap',
                                afterShow: function () {

                                    //$('.tzNice').styler();
                                        $('.submit-tmp').click(function () {
                                            var newId = $('.popTroll form select').val();
                                            finalAjax(id, newId, title);
                                            return false;
                                        });
                                }
                            });
                        }
                    });
                });
                function generatePopUpContent(data, id) {
                    var content = '';
                    var depth =0;
                    parseData(data, depth);
                    function parseData(mass, depth) {
                        var nbsp = '';
                        for (var j=0; j<depth; j++){nbsp = nbsp + '&nbsp;';}
                        for (var i= 0; i < mass.length; i++){
                            content = content + '<option value="'+mass[i].id+'" '+(mass[i].id==id?'selected':'')+'>'+nbsp+mass[i].title+'</option>';
                            if(mass[i].children.length > 0){ parseData(mass[i].children, ++depth); }
                        }
                    }
                    result = '<div class="lPopup popTroll" id="moveItem"><div class="popupTitle">Выберете раздел</div><div class="report-form zForm zNice"><form><div class="zForm-row select"><select class="tzNice" data-smart-positioning ="-1" name="moveItemTo">'+content+'</select></div><div class="zForm-row"><button class="submit-tmp" onsubmit="return false"><span>Выбрать</span></button><a href="#" class="button button_bgay sm-btn closeFancybox">ОТМЕНИТЬ</a></div></form></div></div>';
                    return result;
                }
        
            }
            function finalAjax(id, newId, title) {
                $.ajax({
                    url : "/app_dev.php/actionSection/"+id,
                    dataType:"json",
                    data: {
                        product:{
                            title: title,
                            section: newId
                        }
                    },
                    type:'POST',
                    beforeSend:function () {
                        showPreloader();
                    },
                    success : function(data){
                        $.fancybox.close();
                        if(typeof data.error != 'undefined'){
                            hidePreloader();
                            errorMessage(data.error);
                        } else if (typeof data.success != 'undefined'){
                            location.reload();
                        } else{
                            errorMessage(data);
                        }

                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        location.reload();
                    }
                });
            }
            function errorMessage(data){
                var content = '<div class= "errorMessage"><span>'+data+'</span></div>'
                $.fancybox.open({
                    content: content,
                    padding: 0,
                    fitToView: false,
                    autoSize: true,
                    wrapCSS: 'classWrap',
                    afterClose: function () {
                        location.reload();
                    }
                })
            };
        //END functional move Item
        // functional edit item
            function editItem(item, itemId, parentId) {
                $(document).on('click', '.editItem', function (e) {
                    e.preventDefault();
                    var parent = item.parent();
                    var placeholder = item.text();
                    var old = item;
                    var icon = item.prev();
                    item.remove();
                    parent.html(' <input type="text" required="required" class="zNice" placeholder="'+placeholder+'" name="editName" id="editName" />');
                    $('.smartSearch-modal').css('display','none');

                    parent.find('input').focus().blur(function () {

                        var text = $(this).val();
                        parent.find('input').remove();
                        old.text(text).appendTo(parent);
                        parent.prepend(icon);
                        finalAjaxEdit(itemId, parentId, text);
                    });
                    document.onkeyup = function (e) {
                        e = e || window.event;
                        if (e.keyCode === 13) {
                            parent.find('input').blur();
                        }
                        return false;
                    }

                });
            }
            function finalAjaxEdit(id, newId, title) {
                $.ajax({
                    url : "/app_dev.php/actionSection/"+id,
                    dataType:"json",
                    data: {
                        product:{
                            title: title,
                            section: newId
                        }
                    },
                    type:'POST',
                    beforeSend:function () {
                        showPreloader();
                    },
                    success : function(data){
                        hidePreloader();
                        if(typeof data.error != 'undefined')
                            errorMessage(data.error);
                        else if (typeof data.success != 'undefined')
                            console.log('succes editing');
                        else
                            console.log(data);
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        location.reload();
                    }
                });
            }
        //END functional edit item
        // functional create itemGroup
            function createGroup(parentId) {
                $(document).on('click', '#createGroup button', function (e) {

                    var text = $('#createGroup input[name="newGroup"]').val();
                    if (text != ""){
                        finalAjaxCreateGroup(parentId, text);
                        e.preventDefault();
                        return false;
                    }
                    $('#createGroup input[name="newGroup"]').prev().css('border-color','red');
                    e.preventDefault();
                    return false;
                });
            }
            function finalAjaxCreateGroup(parentId, title) {
                $.ajax({
                    url : "/app_dev.php/actionSection",
                    dataType:"json",
                    data: {
                        product:{
                            title: title,
                            section: parentId
                        }
                    },
                    type:'POST',
                    beforeSend:function () {
                        showPreloader();
                    },
                    success : function(data){
                        $.fancybox.close();
                        if(typeof data.error != 'undefined') {
                            hidePreloader();
                            errorMessage(data.error);
                        }else if (typeof data.success != 'undefined') {
                            console.log('succes creating Group ' + title);
                            location.reload();
                        }else{
                            hidePreloader();
                            errorMessage(data);
                        }
                    },
                    error: function (xhr, ajaxOptions, thrownError) {

                        console.log(xhr);
                        console.log(ajaxOptions);
                        console.log(thrownError);
                        location.reload();

                    }
                });
            }
        // END functional create itemGroup
        // functional create item
                function createItem(parentId) {
                    $(document).on('click', '#createItem button', function (e) {
                        var inputs = $('#createItem input');
                        if (checkInput(inputs)){
                            var price = $('#createItem input[name="priceItem"]').val();
                            var text = $('#createItem input[name="newItem"]').val();

                            finalAjaxCreateItem(parentId, text, price);
                            e.preventDefault();
                            return false;
                        }
                        e.preventDefault();
                        return false;
                    });
                }
                function checkInput(inputs) {
                    var result = true;
                    inputs.each(function () {
                        var text = $(this).val();
                        if (text == ""){
                           $(this).prev().css('border-color','red');
                            result = false;
                        }
                    });
                    return result;


                }
                function finalAjaxCreateItem(parentId, title, price) {
                    $.ajax({
                        url : "/app_dev.php/actionProduct",
                        dataType:"json",
                        data: {
                            product:{
                                title: title,
                                price: price,
                                section: parentId
                            }
                        },
                        type:'POST',
                        beforeSend:function () {
                            showPreloader();

                        },
                        success : function(data){
                            $.fancybox.close();

                            if(typeof data.error != 'undefined') {
                                hidePreloader();
                                errorMessage(data.error);
                            }else if (typeof data.success != 'undefined') {
                                console.log('succes creating item--> ' + title);
                                location.reload();
                            }else{
                                hidePreloader();
                                errorMessage(data);
                            }
                        },
                        error: function (xhr, ajaxOptions, thrownError) {

                            console.log(xhr);
                            console.log(ajaxOptions);
                            console.log(thrownError);
                            location.reload();

                        }
                    });
                }
        // END functional create item
        // functional delete item
            function deleteItem(parentId) {
                $('#deleteItem .item-name').text(' c id: '+parentId);
                $(document).on('click', '#deleteItem button', function (e) {
                    finalAjaxDeleteItem(parentId);
                    e.preventDefault();
                    return false;
                });
            }
            
            function finalAjaxDeleteItem(id) {
                $.ajax({
                    url : "/app_dev.php/actionSection/"+id,
                    dataType:"json",
                    type:'DELETE',
                    beforeSend:function () {
                        showPreloader();
                    },
                    success : function(data){
                        $.fancybox.close();
                        if(typeof data.error != 'undefined') {
                            hidePreloader();
                            errorMessage(data.error);
                        }else if (typeof data.success != 'undefined') {
                            console.log('succes deleted id--> ' + id);
                            location.reload();
                        }else{
                            hidePreloader();
                            errorMessage(data);
                        }
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
            
                        console.log(xhr);
                        console.log(ajaxOptions);
                        console.log(thrownError);
                        location.reload();
            
                    }
                });
            }
        // END functional create item

/*END smart search*/
function showPreloader() {
    $('.preloader').css('display','block');
}
function hidePreloader() {
    $('.preloader').css('display','none');
}
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

/*
* create rival form
*
* @param form
*/
function createRival(form) {
    if(form.valid()){
        var data = form.serialize();
        $.ajax({
            url : "/app_dev.php/actionRival",
            dataType:"json",
            data: data,
            type:'POST',
            success : function(data){
                if(typeof data.error != 'undefined')
                    form.find('.error').text(data.error).show();
                else if (typeof data.success != 'undefined')
                    location.reload();
                else
                    console.log(data);
                debugger;
            },
            error: function (xhr, ajaxOptions, thrownError) {
                console.log(xhr, ajaxOptions, thrownError);
                debugger;
                location.reload();
            }
        });
    }
}

/*
* delete rival
*/
function deleteRivalAlert(id) {
    var content = '<div class="lPopup popTroll" id="moveItem"><div class="popupTitle">Вы уверены в этом действии? При подтверждении удалятся все цены этого конкурента.</div><div class="report-form zForm zNice"><form><div class="zForm-row"><button onclick="deleteRival('+id+'); return false;" class="submit-tmp" onsubmit="return false"><span>Подтвердить</span></button><a href="#" class="button button_bgay sm-btn closeFancybox">ОТМЕНИТЬ</a></div></form></div></div>';
    $.fancybox.open({
        content: content,
        padding:0,
        fitToView:false,
        autoSize:true,
        wrapCSS: 'classWrap'
    });
}

function deleteRival(id) {
    $.ajax({
        url : "/app_dev.php/actionRival/"+id,
        dataType:"json",
        type:'DELETE',
        success : function(data){
            if(typeof data.error != 'undefined')
                errorMessage(data.error);
            else if (typeof data.success != 'undefined')
                location.reload();
            else
                console.log(data);
            debugger;
        },
        error: function (xhr, ajaxOptions, thrownError) {
            console.log(xhr, ajaxOptions, thrownError);
            debugger;
            location.reload();
        }
    });
}