/*table*/
    var ErrorResponse = function (xhr, ajaxOptions, thrownError) {
        console.log([xhr, ajaxOptions, thrownError]);
        debugger;
        hidePreloader();
        errorMessage((ajaxOptions == 'parsererror'?'Ошибка авторизации':'Ошибка отправки запроса'));
    }

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
        $(document).on('click','.wrap-title', function () {
            $(this).find('.icon_lplus').toggleClass('active');
            if($(this).find('.icon_lplus').hasClass('active')){
                $(this).next().addClass('active');
                $(this).next().find('>li').addClass('active');
            }else{
                $(this).next().removeClass('active');
                $(this).next().find('>li').removeClass('active');
            }
        })
    }
    function blockContextMenu(){ //block context menu and call need function
        var el = document.querySelectorAll('.catalogList a');
        for(var i = 0; i<el.length; i++){
            el[i].addEventListener('contextmenu', function(event) {
                if($(this).hasClass('folder_item')){
                    $('.modalWindow.smartSearch-modal li:first-child').attr('style','display:none');
                    $('.modalWindow.smartSearch-modal .creative-menu-punct').removeAttr('style');
                }else{
                    $('.modalWindow.smartSearch-modal li:first-child').removeAttr('style');
                    $('.modalWindow.smartSearch-modal .creative-menu-punct').attr('style','display:none');
                }
                event = event || window.event;
                event.preventDefault ? event.preventDefault() : event.returnValue = false;
                var itemId = $(this).attr('data-id'),
                    parentId = $(this).attr('data-parentid'),
                    title = $(this).text(),
                    object = $(this);
                var pos = $('.catalogList-wrap').offset(),
                    elem_left = pos.left,
                    elem_top = pos.top,
                    Xinner = event.pageX - elem_left,
                    Yinner = event.pageY - elem_top;
                if(!$(this).hasClass('activeCont')){
                    starterContextFunctional(itemId, object, parentId, title);
                }
                $('.catalogList ul a').removeClass('activeCont');
                $(this).addClass('activeCont');
                $('.modalWindow').css({'display':'block','left':Xinner,'top':Yinner}).attr('data-id',itemId);


                return false;
            }, false);
        }
    }
    function starterContextFunctional(itemId, object, parentId, title) {
        createItem(itemId);
        createGroup(itemId);
        editItem(object, itemId, parentId);
        moveItem(itemId, title);
        deleteItem(itemId, title);
    }
    function search() {
        chekForActive();
        plusMinusImg();
        var input = $('#smartSearch');
        input.keyup( function () {
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
                if(val == ""){

                    $('.catalogList ul>li ul').removeClass('active');
                    $('.catalogList ul>li li').removeClass('active');

                    plusMinusImg();
                }
                if(flag == false){resetView();}
            },100);
        });
        function resetView(){
            $('.catalogList ul>li ul').removeClass('active');
            $('.catalogList ul li').removeClass('active');
        }
        function plusMinusImg(){
            $('.icon_lplus').each(
                function () {
                    if($(this).parent().next().hasClass('active')){$(this).addClass('active');}else{$(this).removeClass('active');}
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
                        url:'/sectionList',
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
                                wrapCSS: 'selected-fancybox',
                                afterShow: function () {

                                    $('.tzNice').styler({
                                        selectVisibleOptions:10
                                    });

                                    $(document).on('click', '.submit-tmp', function () {
                                        var newId = $('#moveItem form select').val();
                                        finalAjax(id, newId, title);
                                        return false;
                                    });


                                }
                            });
                        }
                    });
                });
                function generatePopUpContent(data, id) {
                    var content = '<option value="">Корневой раздел</option>';
                    var depth =0;
                    parseData(data, depth);
                    function parseData(mass, depth) {
                        var nbsp = '';
                        for (var j=0; j<depth; j++){nbsp = nbsp + '&nbsp;&nbsp;';}
                        for (var i= 0; i < mass.length; i++){
                            content = content + '<option value="'+mass[i].id+'" '+(mass[i].id==id?'selected':'')+'>'+nbsp+mass[i].title+'</option>';
                            if(mass[i].children.length > 0){ parseData(mass[i].children, ++depth); }
                        }
                    }
                    result = '<div class="lPopup popTroll" id="moveItem"><div class="popupTitle">Выберете раздел</div><div class="zForm zNice"><form><div class="zForm-row select"><select  class="tzNice" data-smart-positioning ="-1" name="moveItemTo">'+content+'</select></div><div class="zForm-row"><button class="submit-tmp" onsubmit="return false"><span>Выбрать</span></button><a href="#" class="button button_bgay sm-btn closeFancybox">ОТМЕНИТЬ</a></div></form></div></div>';
                    return result;
                }
        
            }
            function finalAjax(id, newId, title) {
                $.ajax({
                    url : "/actionSection/"+id,
                    dataType:"json",
                    data: {
                        section:{
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
                            succsesMessage();
                        } else{
                            errorMessage(data);
                        }

                    },
                    error: ErrorResponse
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
            }
        //END functional move Item
        // functional edit group
            function editItem(item, itemId, parentId) {
                $(document).on('click', '.editItem', function (e) {
                    e.preventDefault();
                    var parent = item.parent();
                    var placeholder = item.text();
                    var old = item;
                    var icon = item.prev();
                    item.remove();
                    parent.html(' <input type="text" required="required" class="zNice" placeholder="'+placeholder+'" value="'+placeholder+'" name="editName" id="editName" />');
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
                    url : "/actionSection/"+id,
                    dataType:"json",
                    data: {
                        section:{
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
                        if(typeof data.error != 'undefined') {
                            errorMessage(data.error);
                        }else if (typeof data.success != 'undefined') {
                            hidePreloader();
                            $.fancybox.open({
                                content: '<div class="succes-info">Действие успешно совершено!</div>'
                            });
                        }else {
                            console.log(data);
                        }
                    },
                    error: ErrorResponse
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
var createSectionFlag = false;
function createSectionOrGroup() {
    $(document).on('click', '#createGroupNew .button', function () {
            console.log('wqer');
            universalSubmit($('#createGroupNew form'));
        e.preventDefault();
        return false;
    });
    $('#createGroupNew form').keypress(function (event) {
        if (event.keyCode == 13) {

            universalSubmit($('#createGroupNew form'));
            event.preventDefault();
            return false;

        }
    });
    $('.create-section-bttn').click( function () {
            var num = $(this).data('num');
            createSection(num);
    });


}


            function createSection(folder) {
                var wrap_name = '#createGroupNew';
                $(wrap_name+' .popupTitle').html((folder?'Создать папку':'Создать группу'));
                $(wrap_name+' form').trigger("reset");
                $(wrap_name+' input[name="section[folder]"]').val(folder);
                $(wrap_name+' .zNice-select-text .zNice-select-item').text($(wrap_name+' form select option[value=""]').text());

                $.fancybox.open(wrap_name);
            }

            function finalAjaxCreateGroup(parentId, title) {
                $.ajax({
                    url : "/actionSection",
                    dataType:"json",
                    data: {
                        section:{
                            title: title,
                            section: parentId,
                            folder: 0
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
                            succsesMessage();
                        }else{
                            hidePreloader();
                            errorMessage(data);
                        }
                    },
                    error: ErrorResponse
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
                            var url = $('#createItem input[name="urlItem"]').val();

                            finalAjaxCreateItem(parentId, text, price,url);
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
                function finalAjaxCreateItem(parentId, title, price, url) {
                    
                    $.ajax({
                        url : "/actionProduct",
                        dataType:"json",
                        data: {
                            product:{
                                title: title,
                                price: price,
                                section: parentId,
                                url: url
                            }
                        },
                        type:'POST',
                        beforeSend:function () {
                            showPreloader();

                        },
                        success : function(data){
                            $.fancybox.close();
                            createItemFlag = false;
                            if(typeof data.error != 'undefined') {
                                hidePreloader();
                                errorMessage(data.error);
                            }else if (typeof data.success != 'undefined') {
                                succsesMessage();
                            }else{
                                hidePreloader();
                                errorMessage(data);
                            }
                        },
                        error: ErrorResponse
                    });

                }
                function editTovar(obj) { //редактирование товара
                    $(document).on('click', '.editingTovar', function () {

                        var id = obj.data('id');
                        var parentid = obj.data('section');
                        var title = obj.data('name');
                        var price = obj.data('price');
                        var url = obj.data('url');

                        var popup = $('#editTovar');
                        popup.find('input[name=name]').val(title);
                        popup.find('input[name=id]').val(id);
                        popup.find('input[name=parentid]').val(parentid);
                        popup.find('input[name=price]').val(price);
                        popup.find('input[name=url]').val(url);

                        $.fancybox.open(popup);
                    });


                }
                function editTovarAjax() {
                    $(document).on('click', '#editTovar button', function (e) {
                        var popup = $('#editTovar');
                        var title = popup.find('input[name=name]').val();
                        var id = popup.find('input[name=id]').val();
                        var parentid = popup.find('input[name=parentid]').val();
                        var price = popup.find('input[name=price]').val();
                        var url = popup.find('input[name=url]').val();

                        $.ajax({
                            url: "/actionProduct/" + id,
                            dataType: "json",
                            data: {
                                product: {
                                    title: title,
                                    price: price,
                                    section: parentid,
                                    url: url
                                }
                            },
                            type: 'POST',
                            beforeSend: function () {
                                showPreloader();

                            },
                            success: function (data) {
                                $.fancybox.close();

                                if (typeof data.error != 'undefined') {
                                    hidePreloader();
                                    errorMessage(data.error);
                                } else if (typeof data.success != 'undefined') {
                                    succsesMessage();
                                } else {
                                    hidePreloader();
                                    errorMessage(data);
                                }
                            },
                            error: ErrorResponse
                        });
                        e.preventDefault();
                        return false;
                    });
                }
                function deleteTovar(id, text) { //удаление товара
                    $('#deleteTovar .item-name').text('"'+text+'"');
                    $(document).on('click', '#deleteTovar button', function (e) {
                        finalAjaxDeleteTovar(id);
                        e.preventDefault();
                        return false;
                    });
                }
                function finalAjaxDeleteTovar(id) {
                    $.ajax({
                        url : "/app_dev.php/actionProduct/"+id,
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
                                succsesMessage();
                            }else{
                                hidePreloader();
                                errorMessage(data);
                            }
                        },
                        error: ErrorResponse
                    });
                }
        // END functional create item
        // functional delete item
            function deleteItem(parentId, text) {
                $('#deleteItem .item-name').text('"'+text+'"');
                $(document).on('click', '#deleteItem button', function (e) {
                    finalAjaxDeleteItem(parentId);
                    e.preventDefault();
                    return false;
                });
            }
            
            function finalAjaxDeleteItem(id) {
                $.ajax({
                    url : "/actionSection/"+id,
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
                            succsesMessage();
                        }else{
                            hidePreloader();
                            errorMessage(data);
                        }
                    },
                    error: ErrorResponse
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


/*
* create rival form
*
* @param form
*/
function selectToInput(popup) {

    var select = popup.find('select');
    var input = popup.find('input[name="rival[codeText]"]');

    var delButtn = popup.find('.deletius');
    var id = select.val();
    var txt = select.find('option[value='+id+']').text();
    delButtn.removeClass('notactive').attr({
        'data-id':id,
        'data-text':txt
    });
    input.val(txt);
    input.keyup( function () {
        delButtn.addClass('notactive');
    });

    select.change(function () {

        var id = select.val();
        var txt = select.find('option[value='+id+']').text();
        delButtn.removeClass('notactive').attr({
            'data-id':id,
            'data-text':txt
        });
        input.val(txt);
    });

}

function createRivalInPrice(form) {


        if(form.find('select').val()>0){
            var id = form.find('select').val();
            var section = form.find('input[name="rival[section][0]"]').val();
            $.ajax({
                url: '/app_dev.php/activeRivalSection/' + id,
                dataType: "json",
                data: {
                    section: section //active section identifier
                },
                type: 'POST',
                beforeSend: function () {
                    showPreloader();
                },
                success: function (data) {
                    $.fancybox.close();
                    if (typeof data.error != 'undefined') {
                        hidePreloader();
                        errorMessage(data.error);
                    } else if (typeof data.success != 'undefined') {
                        succsesMessage();
                    } else {
                        hidePreloader();
                        console.log(data);
                    }


                },
                error: ErrorResponse
            });
        }else{
            if(form.valid()) {
                var data = form.serialize();
                $.ajax({
                    url: form.data('action'),
                    dataType: "json",
                    data: data,
                    type: 'POST',
                    beforeSend: function () {
                        showPreloader();
                    },
                    success: function (data) {
                        $.fancybox.close();
                        if (typeof data.error != 'undefined') {
                            hidePreloader();
                            errorMessage(data.error);
                        } else if (typeof data.success != 'undefined') {
                            succsesMessage();
                        } else {
                            hidePreloader();
                            console.log(data);
                        }


                    },
                    error: ErrorResponse
                });
            }


        }


}

function createRival(form) {
    
    if(form.valid()){
        var data = form.serialize();
        $.ajax({
            url : form.data('action'),
            dataType:"json",
            data: data,
            type:'POST',
            beforeSend:function () {
                showPreloader();
            },
            success : function(data){
                $.fancybox.close();
                if(typeof data.error != 'undefined'){
                    hidePreloader();
                    errorMessage(data.error);
                }else if (typeof data.success != 'undefined'){
                    succsesMessage();
                } else{
                    hidePreloader();
                    console.log(data);
                }


            },
            error: ErrorResponse
        });
    }
}

/*
* delete rival
*/
function deleteRivalAlert(id) {
    var content = '<div class="lPopup popTroll" id="moveItem"><div class="popupTitle"><p>Вы уверены в этом действии?</p><p><span class="subtitle"> При подтверждении удалятся все цены этого конкурента.</span></p></div><div class="zForm zNice"><form><div class="zForm-row"><button onclick="deleteRival('+id+'); return false;" class="submit-tmp" onsubmit="return false"><span>Подтвердить</span></button><a href="#" class="button button_bgay sm-btn closeFancybox">ОТМЕНИТЬ</a></div></form></div></div>';
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
        url : "/actionRival/"+id,
        dataType:"json",
        type:'DELETE',
        beforeSend:function () {
            showPreloader();
        },
        success : function(data){
            $.fancybox.close();
            if(typeof data.error != 'undefined')
            {
                hidePreloader();
                errorMessage(data.error);

            }
            else if (typeof data.success != 'undefined')
                succsesMessage();
            else{
                hidePreloader();
                console.log(data);
            }


        },
        error: ErrorResponse
    });
}
// edit active Section
    function editActiveSection() {
        $(document).on('click', '.editActiveSection', function (e) {
            e.preventDefault();
            var span = $(this).prev();
            var parent = $(this).parent();
            var placeholder = span.text();
            console.log(parent);
            parent.prepend(' <input type="text" required="required" class="zNice" placeholder="'+placeholder+'" value="'+placeholder+'" name="editName"/>');
            $(this).prev().remove();
            var parentId = $(this).data('parentid');
            var itemId = $(this).data('id');
            parent.find('input').focus().blur(function () {

                var text = $(this).val();
                parent.find('input').remove();
                span.text(text).prependTo(parent);
                $('.catalogList a[data-id="'+itemId+'"]').text(text);
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
//END  edit active Section
// EDITING concurent price table
    function targetBlanc(){
        $(document).on('click', '#productEdit input[name=url]', function () {
            var href = $(this).val();
            window.open(href, '_blank');
        });

    }
    function infoCell() {
        $(document).on('click', '.editablePopup', function () {
            
            
            var codeId = $(this).data('price-code');
            var rival = $(this).data('rival-id');
            var code = revalCodes[rival][codeId].title;
            var title = $(this).data('price-title');
            var url = $(this).data('price-url');
            var date =$(this).data('price-date');
            var priceid =$(this).data('price-id');
            var productId = $(this).closest('.table-row').data('id');
            var popup = $('#productEdit');

            popup.find('input[name=name]').val(title);
            popup.find('input[name=code]').val(code);
            popup.find('input[name=url]').val(url);
            popup.find('.productEdit-date').text(date);
            popup.find('input[name=product]').val(productId);
             $.fancybox.open(popup);
            editInfoCell($(this), priceid);
            refreshPrice(priceid);

        });
    }
    function editInfoCell(obj, priceid) { // редактирование ячейки цены конкурента
        $(document).on('click', '.productEdit .editButton', function (e) {
            var codeId = obj.data('price-code');
            var rival = obj.data('rival-id');
            var url = obj.data('price-url');
            var mass = revalCodes[rival];
            var options = '';
            var productName = obj.closest('.table-row').data('product-name');
            var productId = obj.closest('.table-row').data('id');
            for (var key in mass) {

                options = options + '<option value="'+key+'" '+(key==codeId?'selected':'')+'>'+mass[key].title+'</option>';
            }
            options = '<select>'+options+'</select>';
            var popup = $('#creting-price');
            popup.find('input[name=url]').val(url);
            popup.find('input[name=name]').val(productName);
            popup.find('input[name=product]').val(productId);
            popup.find('input[name=priceid]').val(priceid);
            popup.find('input[name=rival]').val(rival);
            popup.find('.select').html(options);
            popup.find('select').styler();
            popup.find('.popupTitle').text('Редактировать цену');
            selectToInput(popup);
            $.fancybox.open(popup,{
                wrapCSS: 'selected-fancybox'
            });

            e.preventDefault();

        });
    }
    function refreshPrice(id) {
        $(document).on('click', '.productEdit .refreshButton', function () {
            finalAjaxRefresh(id);

        });
    }
function finalAjaxRefresh(id) {
    $.ajax({
        url : "/actionPriceParse/"+id,
        dataType:"json",
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

                succsesMessage();
            }else{
                hidePreloader();
                errorMessage(data);
            }
        },
        error: ErrorResponse
    });
}
    function createCell() {  // нередактированая ячейка цены конкурента
        $(document).on('click', '.creatingPopup', function () {

            var rival = $(this).data('rival-id');
            var mass = revalCodes[rival];
            var options = '';
            var productName = $(this).closest('.table-row').data('product-name');
            var productId = $(this).closest('.table-row').data('id');
            for (var key in mass) {

                    options = options + '<option value="'+key+'" '+(mass[key].selected>0?'selected':'')+'>'+mass[key].title+'</option>';
            }
            options = '<select>'+options+'</select>';
            var popup = $('#creting-price');
            popup.find('input[name=name]').val(productName);
            popup.find('input[name=product]').val(productId);
            popup.find('input[name=rival]').val(rival);
            popup.find('.select').html(options);
            popup.find('select').styler();
            selectToInput(popup);
            $.fancybox.open(popup,{
                wrapCSS: 'selected-fancybox'
            });

            

        });
    }
    function createPrice() { //создание цены

        $(document).on('click', '#creting-price button', function (e) {
            if($(this).closest('form').valid()){
                var popup = $('#creting-price');
                var product = popup.find('input[name="product"]').val();
                var title = popup.find('input[name="name"]').val();
                var url = popup.find('input[name="url"]').val();
                var rival = popup.find('input[name="rival"]').val();
                var code = popup.find('input[name="rival[codeText]"]').val();
                var priceid = popup.find('input[name="priceid"]').val();
                var ajaxurl = (priceid.length>0?"/app_dev.php/actionPrice/"+priceid:"/app_dev.php/actionPrice");
                console.log(ajaxurl);
                $.ajax({
                    url : ajaxurl,
                    dataType:"json",
                    data: {
                        price: {
                            rival:rival,
                            product: product,
                            code: code,
                            url: url,
                            title: title
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
                            hidePreloader();

                            finalAjaxRefresh(data.id)

                        }else{
                            hidePreloader();
                            errorMessage(data);
                        }
                    },
                    error: ErrorResponse
                });
            }

            e.preventDefault();
            return false;
        });
    }

//  END END EDITING concurent price table

//
function universalSubmit(form) {
    
    if(form.valid()){
        var data = form.serialize();
        $.ajax({
            url : form.data('action'),
            dataType:"json",
            data: data,
            type:'POST',
            beforeSend:function () {
                showPreloader();
            },
            success : function(data){
                
                $.fancybox.close();
                if(typeof data.error != 'undefined'){
                    hidePreloader();
                    errorMessage(data.error);
                }else if (typeof data.success != 'undefined'){
                    succsesMessage();

                } else{
                    hidePreloader();
                    console.log(data);
                }
            },
            error: ErrorResponse
        });
    }
}
//
//логика чекбоксов в попапе конкурентов
function checScript() {
    $(document).on('change', '.check-script input', function () {
        var form = $(this).closest('form');
        if($(this).prop('checked')){

           recursive($(this), form);
        }else{
            recursive2($(this), form);
        }
        function recursive(elem, form) {
                var parent = elem.val();
                console.log(parent);
                if (parent.length>0) {
                    form.find('.check-script input[data-parent-id = ' + parent + ']').each(function () {
                        $(this).prop('checked', true);
                        recursive($(this), form);
                    });
                }
        }
        function recursive2(elem, form) {
            var parent = parseInt(elem.data('parent-id'));
            console.log(parent);
            if (parent>0) {

                form.find('.check-script input[value = ' + parent + ']').each(function () {
                    $(this).prop('checked', false);
                    recursive2($(this), form);
                });
            }
        }




    })
}
// END логика чекбоксов в попапе конкурентов
//удаление кода
function deleteCode() {
    $('.deletius').click(function () {
        var text = $(this).data('text');
        var id = $(this).data('id');
        $('#deleteCode .item-name').text('"'+text+'"');
        $.fancybox.open('#deleteCode');
        $(document).on('click', '#deleteCode button', function (e) {
            finalAjaxDeleteCode(id);
            e.preventDefault();
            return false;
        });
    });

}
function finalAjaxDeleteCode(id) {
    $.ajax({
        url : "/app_dev.php/actionCode/"+id,
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
                succsesMessage();
            }else{
                hidePreloader();
                errorMessage(data);
            }
        },
        error: ErrorResponse
    });
}
// END удаление кода
//контекстное меню товара
function contextMenuTovar() {
    var el = document.querySelectorAll('.table-new-one .table-row');
    for(var i = 0; i<el.length; i++){
        el[i].addEventListener('contextmenu', function(event) {

            event = event || window.event;
            event.preventDefault ? event.preventDefault() : event.returnValue = false;
            var obj = $(this);
            var id = obj.data('id');
            var text = obj.data('name');
            var section = obj.data('section');

            var pos = $('.tables-wraper').offset(),
                elem_left = pos.left,
                elem_top = pos.top,
                Xinner = event.pageX - elem_left,
                Yinner = event.pageY - elem_top;
            if(!$(this).hasClass('activeCont')){
                starterContextTovarFunctional(obj, id, text);
                updateMenuTovar(id, section);
            }
            $('.table-new-one .table-row').removeClass('activeCont');
            $(this).addClass('activeCont');
            $('.modalWindow-tovar').css({'display':'block','left':Xinner,'top':Yinner}).attr('data-id',id);


            return false;
        }, false);
    }
}
function updateMenuTovar(itemId, sectid) {
    $(document).on('click', '.updateTovar', function () {
        $.ajax({
            url : "/app_dev.php/actionSectionParse/"+sectid,
            data:{
                product:itemId
            },
            type:'POST',
            beforeSend:function () {
                showPreloader();

            },
            success : function(data){
                if(typeof data.error != 'undefined') {
                    hidePreloader();
                    errorMessage(data.error);
                }else if (typeof data.success != 'undefined') {
                    succsesMessage();
                }else{
                    hidePreloader();
                    errorMessage(data);
                }
            },
            error: ErrorResponse
        });
    });
}
function starterContextTovarFunctional(obj, id, text) {
    editTovar(obj);
    $(document).on('click', '.createConcurent', function () {
        var hidden = obj.data('section');
        $('#addConcurent input[name="rival[section][0]"]').val(hidden);
        $.fancybox.open('#addConcurent',{
            wrapCSS: 'selected-fancybox',
            afterShow:function () {
                
                    var select = $('#addConcurent').find('select');
                    select.change(function () {
                        if(select.val()>0){
                            $('.locked-to-hide').stop().slideUp();
                        }else{
                            $('.locked-to-hide').stop().slideDown();
                        }
                    });
                
            }
        });
    });
    $(document).on('click', '.moveUpTovar', function () {
        var z = 1;
        finalAjaxMoveTovar(id, z);
    });
    $(document).on('click', '.moveDownTovar', function () {
        var z = 0;
        finalAjaxMoveTovar(id, z);
    });
    $(document).on('click', '.deletingTovar', function () {
               deleteTovar(id,text);
    });

}
function finalAjaxMoveTovar(id, z) {
    $.ajax({
        url : "/app_dev.php/actionProductPos/"+id,
        data:{
            up:z
        },
        type:'POST',
        beforeSend:function () {
            showPreloader();

        },
        success : function(data){
           if(typeof data.error != 'undefined') {
                hidePreloader();
                errorMessage(data.error);
            }else if (typeof data.success != 'undefined') {
               succsesMessage();
            }else{
                hidePreloader();
                errorMessage(data);
            }
        },
        error: ErrorResponse
    });
}
//END контекстное меню товара
//перемещение цен конкурента 
function contextMenuConcurent() {
    var el = document.querySelectorAll('.context-conc');
    for(var i = 0; i<el.length; i++){
        el[i].addEventListener('click', function(event) {

            event = event || window.event;
            event.preventDefault ? event.preventDefault() : event.returnValue = false;
            var obj = $(this).closest('.table-td');
            var id = obj.data('id');
            var itemId = $(this).closest('.table-td').data('id');
            var itemName = $(this).closest('.table-td').data('name');
            var sectid = $(this).closest('.table-head').data('section');
            var sectName = $(this).closest('.table-head').data('name');

            var pos = $('.new-table-price').offset(),
                elem_left = pos.left,
                elem_top = pos.top,
                Xinner = event.pageX - elem_left,
                Yinner = event.pageY - elem_top;
            if(!$(this).hasClass('activeCont')){
                moveConc(itemId, sectid);
                updateConc(itemId, sectid);
                delConc(itemId, sectid, itemName, sectName);
            }
            $('.context-conc').removeClass('activeCont');
            $(this).addClass('activeCont');

            var traktorLevo = $('.new-table-price').offset().left - $('.table-new-two').offset().left ;

            $('.modalWindow-conc').css({'display':'block','left':Xinner + traktorLevo ,'top':Yinner}).attr('data-id',id);


            return false;
        }, false);
    }
}
function updateConc(itemId, sectid) {
    $(document).on('click', '.updateConc', function () {
        $.ajax({
            url : "/app_dev.php/actionSectionParse/"+sectid,
            data:{
                rival:itemId
            },
            type:'POST',
            beforeSend:function () {
                showPreloader();

            },
            success : function(data){
                if(typeof data.error != 'undefined') {
                    hidePreloader();
                    errorMessage(data.error);
                }else if (typeof data.success != 'undefined') {
                    succsesMessage();
                }else{
                    hidePreloader();
                    errorMessage(data);
                }
            },
            error: ErrorResponse
        });
    });
}
function moveConc(itemId, sectid) {

    $(document).on('click', '.moveConc', function () {

        var content= '';
        $('.table-new-two .table-head .table-td').each(function () {
            var id = $(this).data('id');
            var name = $(this).data('name');
            if(itemId == id){
                content = content + '<div class="item-draggable active" data-id="'+id+'"><span>'+name+'</span></div>';
            }else {
                content = content + '<div class="item-draggable" data-id="' + id + '"><span>' + name + '</span></div>';
            }
        });
        $('#moveConc .list-items').html(content);
        $('#moveConc input[type="hidden"]').val(sectid);

        $.fancybox.open('#moveConc', {
            afterShow: function () {
                $('#moveConc .list-items').sortable();
            }
        });
        $(document).on('click', '#moveConc button', function () {
            var mass = [];
            var id = $('#moveConc input[type="hidden"]').val();
            $('#moveConc .item-draggable').each(function () {
                mass.push($(this).data('id'));
            });

            $.ajax({
                url : "/app_dev.php/actionSectionPos/"+id,
                data:{
                    rivals:mass
                },
                type:'POST',
                beforeSend:function () {
                    showPreloader();

                },
                success : function(data){
                    if(typeof data.error != 'undefined') {
                        hidePreloader();
                        errorMessage(data.error);
                    }else if (typeof data.success != 'undefined') {
                        succsesMessage();
                    }else{
                        hidePreloader();
                        errorMessage(data);
                    }
                },
                error: ErrorResponse
            });

        });
    });

}
function succsesMessage(){
    hidePreloader();
    $.fancybox.open({
        content:'<div class="succes-info">Действие успешно совершено!</div>',
        afterClose:function () {
            showPreloader();
            location.reload();
        }
    });
}
function delConc(itemId, sectid, itemName, sectName) {
    $(document).on('click', '.delConc', function () {

        var popup = $('#deleteConc');
        popup.find('.item-name').text('"'+itemName+'"');
        popup.find('.sect-name').text('"'+sectName+'"');
        $.fancybox.open(popup);
        $(document).on('click', '#deleteConc button', function () {
            $.ajax({
                url : "/app_dev.php/sectionRival/"+sectid,
                data:{
                    rival:itemId
                },
                type:'DELETE',
                beforeSend:function () {
                    showPreloader();

                },
                success : function(data){
                    if(typeof data.error != 'undefined') {
                        hidePreloader();
                        errorMessage(data.error);
                    }else if (typeof data.success != 'undefined') {

                        succsesMessage();
                    }else{
                        hidePreloader();
                        errorMessage(data);
                    }
                },
                error: ErrorResponse
            });
        });

    });

}

//END перемещение цен конкурента

function missClick(div) {
    $(document).on('click touchstart',function (event){
            if (!div.is(event.target) && div.has(event.target).length === 0 ){ div.removeAttr('style');}
    });
}

function closeContext() {
    $('.modal-close').click(function () {
        $(this).closest('.conc-modal').removeAttr('style');
    });

}
var createItemFlag = false;
function createItemButton() {
       $('.createItemButton').click(function () {
           if(createItemFlag==false) {
               createItemFlag = true;
               var section = $(this).data('section');
               createItem(section);
           }
       });
}

editTovarAjax();
createCell();
createPrice();
infoCell();
editActiveSection();
targetBlanc();
$(document).ready(function () {


    checScript();
    hoverTableRow();
    oneWidth();
    setTimeout(function () {
        oneHeight();
    },100);
    clickOnPlus();
    blockContextMenu();
    search();
    deleteCode();
    contextMenuTovar();
    contextMenuConcurent();
    closeContext();
    createSectionOrGroup();
    $('.miss').each(function () {
        missClick($(this));
    });
    createItemButton();

    $(document).on('click', function( event ){

        if ( !$('.modalWindow-tovar').is(event.target) && $('.modalWindow-tovar').has(event.target).length === 0 ){

            $('.modalWindow-tovar').css('display', "none");

        };     

        if ( !$('.modalWindow-conc').is(event.target) && $('.modalWindow-conc').has(event.target).length === 0 &&  !$('.icon_edit').is(event.target) ){

            if ( $('.modalWindow-conc').css('display') == "block" ){

                $('.modalWindow-conc').css('display', "none");

            }

        };          

    });



});

$(window).resize(function(){

    oneWidth();

});

