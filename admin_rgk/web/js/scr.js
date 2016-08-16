var scroller=jQuery.browser.webkit ? "body": "html";

/* modernize */
function modernize() {
	// placeholder 
	if(!Modernizr.input.placeholder){
		$('[placeholder]').each(function() {
			$(this).watermark($(this).attr('placeholder'));
		});
	}
}

function alertPopup(popupContent,timeClose,classWrap){
	$.fancybox.open({
		content: '<div class="alertPopup">'+popupContent+'</div>',
		padding:0,
		fitToView:false,
		autoSize:true,
		wrapCSS: classWrap
	});
}


/* input only Number  */
function inputNumber(block) {	
	$('input', block).keypress(function(e) {
		if (e.which >= 47 && e.which <= 57 ){}
		else return false;
	});
	
	$('input', block).keyup(function() {
		$inputNum = $(this);
		if ($inputNum.val == '' || $inputNum.val() == 0) {
			$inputNum.val('1'); 
		}
	});
}

function initDatepicker(){
	// простой datepicker
	$( ".datepicker" ).datepicker({
        monthNames: ['Январь','Февраль','Март','Апрель','Май','Июнь',
        'Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'],
        monthNamesShort: ['Янв','Фев','Мар','Апр','Май','Июн',
        'Июл','Авг','Сен','Окт','Ноя','Дек'],
        dayNames: ['воскресенье','понедельник','вторник','среда','четверг','пятница','суббота'],
        dayNamesShort: ['вск','пнд','втр','срд','чтв','птн','сбт'],
        dayNamesMin: ['Вс','Пн','Вт','Ср','Чт','Пт','Сб'],
        weekHeader: 'Не',
        dateFormat: 'dd.mm.yy',
        firstDay: 1,
        isRTL: false,
        showMonthAfterYear: false,
        yearSuffix: '',
        showAnim:'slideDown',
        showOtherMonths: true,
        onSelect: function(date){
        	var parent = $(this).closest('.choiceClasses');
        	$('.youChoose-hall-date', parent).text(date);
        	$('.youChoose-hall-date', parent).closest('.youChoose-item').removeClass('hidden');
        }
	});

	// datepickers для выбора диапазона дат

	// datepickers дата 'от'
	$( "#dateFrom" ).datepicker({
		monthNames: ['Январь','Февраль','Март','Апрель','Май','Июнь',
        'Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'],
        monthNamesShort: ['Янв','Фев','Мар','Апр','Май','Июн',
        'Июл','Авг','Сен','Окт','Ноя','Дек'],
        dayNames: ['воскресенье','понедельник','вторник','среда','четверг','пятница','суббота'],
        dayNamesShort: ['вск','пнд','втр','срд','чтв','птн','сбт'],
        dayNamesMin: ['Вс','Пн','Вт','Ср','Чт','Пт','Сб'],
        weekHeader: 'Не',
        dateFormat: 'dd.mm.y',
        firstDay: 1,
        isRTL: false,
        showMonthAfterYear: false,
        yearSuffix: '',
        showAnim:'slideDown',
        showOtherMonths: true,
		onSelect: function(date) {
			$( "#dateTo" ).datepicker( "option", "minDate", date );
		}
	})
	//$( "#dateFrom" ).not('.noMinDate').datepicker( "option",  );
	// datepickers дата 'до'
	$( "#dateTo" ).datepicker({
		monthNames: ['Январь','Февраль','Март','Апрель','Май','Июнь',
        'Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'],
        monthNamesShort: ['Янв','Фев','Мар','Апр','Май','Июн',
        'Июл','Авг','Сен','Окт','Ноя','Дек'],
        dayNames: ['воскресенье','понедельник','вторник','среда','четверг','пятница','суббота'],
        dayNamesShort: ['вск','пнд','втр','срд','чтв','птн','сбт'],
        dayNamesMin: ['Вс','Пн','Вт','Ср','Чт','Пт','Сб'],
        weekHeader: 'Не',
        dateFormat: 'dd.mm.y',
        firstDay: 1,
        isRTL: false,
        showMonthAfterYear: false,
        yearSuffix: '',
        showAnim:'slideDown',
        showOtherMonths: true,
		onSelect: function(date) {
			$( "#dateFrom" ).datepicker( "option", "maxDate", date );
		}
	})
	//$( "#dateTo" ).not('.noMinDate').datepicker( "option", "minDate", 0 );
}


/* u_tabs */
function u_tabs(link, block) {
	$(link).click(function(e) {
		var $currentTab = $(this);
		var tabId = $currentTab.data('utab');

		$(link).removeClass('active');
		$currentTab.addClass('active');

		$(block).hide().removeClass('active');
		$(block+'[data-utab="' + tabId + '"]').show().addClass('active');
		if($(link).is('a')){
			e.preventDefault();
		}
	});
	$(link).eq(0).click();
}

function minHeightPage(){
	var windHeight = $(window).height(),
		headerHeight = $('.header').outerHeight()+$('.topLine-bottom').outerHeight(),
		height = windHeight-headerHeight,
		blocks = $('.sidebar_w,.content_w,.content-left'),
		mainHeight = $('.main').height();
	if(mainHeight>height) height = mainHeight;
	blocks.css({minHeight:height});
}
function addActiveParent(el){
	el.click(function(){
		$(this).parent().toggleClass('active');
		return false;
	})
}




/* DOCUMENT READY  */
$(document).ready(function() {	
	modernize();
	minHeightPage();
	addActiveParent($('.catalogList-title'));
	
	initDatepicker();

	$(document).click(function (e) {
	    var container = $(".modalWindow");
	    if (container.has(e.target).length === 0){
	        container.hide(); 
	    }
	});

	$('.fancybox-popup').fancybox({
		padding:20,
		fitToView:false,
		autoSize:true 
	});

	u_tabs('.tabs-buttons a','.tabs-window');

	$('.closeFancybox').click(function(){
		$.fancybox.close();
		return false;
	});

	$('.sTable tbody tr').click(function(){
		$('.sTable tbody tr').removeClass('active');
		$(this).addClass('active');
	})

	$('.productEdit .editButton').click(function(){
		var inputs = $(this).closest('form').find('input'),
			parent = $(this).closest('.productEdit');
		
		if(parent.is('.active')){
			parent.removeClass('active');
			inputs.attr('readonly',true);
		}
		else{
			parent.addClass('active');
			inputs.removeAttr('readonly');
		}
		return false;
	});
	$('.productEdit .refreshButton').click(function(){
		var inputs = $(this).closest('form').find('input'),
			parent = $(this).closest('.productEdit');
		inputs.attr('readonly',true);
		parent.removeClass('active');
		return false;
	})

	$(document).on('click','.Add-on_price table tr',function(){
		var index = $(this).index(),
			parent = $(this).closest('.Add-on_price'),
			form = $('#productEdit'),
			prodName = $(this).find('.prodName').text(),
			prodsite = $(this).find('.prodName').attr('data-site'),
			prodCat = $(this).find('.prodCategory').text();
		$('.productEdit input').attr('readonly',true);
		$('.productEdit').removeClass('active');
		$('.productEdit-name input').val(prodName)
		$('.productEdit-cat input').val(prodCat);
		$('.productEdit-url input').val(prodsite);
		$('.productId').val(index);
		$.fancybox.open({
			content:form,
			fitToView:false,
			autoSize:true
		});


	})
});

$(window).resize(function() {
	minHeightPage();
});
