(function ($) {
	'use strict'; // Global Vars

	var $document = $(document);

	var scheduleSettings = {
		// days: [2, 3, 4, 5, 6, 0, 1],
		startTime: '0:00',
		endTime: '24:00',
		interval: 60
	};

	var cfAdminConditions = {

		init: function init() {
			cfAdminConditions.initObjects();
			cfAdminConditions.clickEvents();
			cfAdminConditions.changeEvents();
			cfAdminConditions.submitEvents();
			cfAdminConditions.keyUpEvents();
		},

		submitEvents: function submitEvents() {
			$('.post-type-cf_cc_condition #post').on('submit', function () {
				// Updating all the schedule data with their correspond hidden input
				$('.date-time-schedule').each(function () {
					var $elem = $(this);
					var $parent = $elem.parent();
					var scheduleInput = $parent.find('.schedule-input');
					scheduleInput.val(JSON.stringify($elem.data('artsy.dayScheduleSelector').serialize()));
				});
			});
		},

		changeEvents: function changeEvents() {
			$document.on('change', '.rule-wrap select', function () {
				var $select = $(this);
				var $selectedOption = $select.find(':selected');
				var $adminWrapper = $('.admin-conditions-wrap');
				var $triggerData = $selectedOption.data('show-fields');

				if (typeof $triggerData !== 'undefined') {
					var $triggerDataClasses = $triggerData.replace(/\|/g, ' ');


					if ($select.hasClass('second-level-selection')) {
						var $selectionContainer = $select.closest('.second-level-selection-container');
						$selectionContainer.attr('class', 'cf-cc-form-group second-level-selection-container').addClass($triggerDataClasses);
					}

					$adminWrapper.attr('class', 'admin-conditions-wrap').addClass($triggerDataClasses);

					$('#hidden_stored_selection_classes').val($triggerDataClasses);
				}
			});

			$document.on('change click', '.cf-cc-autocomplete-opener', function () {
				var $this = $(this);
				var effectRate = 250;
				var $currentShownElem = $('.cf-cc-geo-selected');
				var classNameOfElemToShow;
				var $elemToShow;
				$currentShownElem.stop(true).slideUp(effectRate);
				$currentShownElem.removeClass('cf-cc-geo-selected'); // Handle new element

				classNameOfElemToShow = $this.data('open');
				$elemToShow = $('.' + classNameOfElemToShow);
				$elemToShow.addClass('cf-cc-geo-selected');
				$elemToShow.stop(true).slideDown(effectRate);
			});
		},

		clickEvents: function clickEvents() {
			$document.on('click', '.conditional-tabs .tab', function () {
				var correspondingSelect = $(this).data('trigger');

				if (typeof correspondingSelect !== 'undefined') {
					$('.conditional-tabs').attr('class', 'conditional-tabs').addClass(correspondingSelect);
					$('.trigger-type').val(correspondingSelect).trigger('change');
				}
			});

			$document.on('click', '.settimeinstructions .closeX', function () {
				// write cookie to the client in order to prevent re-displaying of the info box
				cfAdminConditions.createCookie('set_time_instructions', true, 712); // 712 days - 2 years
				// remove the box from the view

				$(this).closest('.set-time-info-container').remove();
			});
		},

		keyUpEvents: function keyUpEvents() {
			// update query string text in the instruction box
			$document.on('keyup', "input[data-field='url-custom']", function () {
				var $input = $(this);
				var inputValue = $input.val();
				var isValid = true;
				var queryStringTyped;
				$("input[data-field='url-custom']").not($input).each(function () {
					var $inputAuxValue = $(this).val();

					if ($inputAuxValue !== '') {
						if (inputValue === $inputAuxValue) {
							isValid = false;
						}
					}
				});

				if (!isValid) {
					// handle invalid query string
					$input.closest('.form-group').addClass('has-danger').addClass('has-error');
					$input.after('<div class="help-block">' + CFCCAdmin.text.duplicatedQueryString + '</div>');
					$('#publishing-action').append('<div class="query-string-err-notification">' + CFCCAdmin.text.duplicatedQueryStringOnPublish + '!</div>');
				} else {
					var $formGroup = $input.closest('.form-group');

					// query string is valid
					$formGroup.removeClass('has-danger').removeClass('has-error');
					$formGroup.find('.help-block').remove();
					$('#publishing-action .query-string-err-notification').remove();
				}

				queryStringTyped = inputValue === '' ? 'your-query-string' : inputValue;
				$input.closest('.rule-wrap').find('.instructions b').text(queryStringTyped);
			}); // update query string text in the instruction box
		},

		initObjects: function initObjects() {
			$('.post-type-cf_cc_condition #post').attr('novalidate', 'novalidate');

			$('.date-time-schedule').dayScheduleSelector(scheduleSettings); // Enable DateTimePicker

			$('.cfdatetimepicker').cfdatetimepicker(); // set custom Add New link active

			if (window.location.href.indexOf('post-new.php?post_type=cf_cc_condition') > -1) {
				$('a[href="' + window.location.href + '"]').closest('li').addClass('current');
			}

			var $elementsToEnableSelection = $('.query-string-code, .cf-cc-dynamic-link-code, .wp-editor-area');

			$('#cf-cc-versions-container .cf-cc-versions-sortable').disableSelection();
			$elementsToEnableSelection.on('mouseenter', function () {
				$('#cf-cc-versions-container .cf-cc-versions-sortable').enableSelection();
			});
			$elementsToEnableSelection.on('mouseleave', function () {
				$('#cf-cc-versions-container .cf-cc-versions-sortable').disableSelection();
			});
			$('.cf-cc-versions-sortable').keydown(function (e) {
				if (e.keyCode === 65 && e.ctrlKey) {
					e.target.select();
				}
			});
		},

		// Create cookie
		createCookie: function createCookie(name, value, days) {
			var expires;
			var date;

			if (days) {
				date = new Date();
				date.setTime(date.getTime() + days * 24 * 60 * 60 * 1000);
				expires = '; expires=' + date.toGMTString();
			} else {
				expires = '';
			}

			document.cookie = name + '=' + value + expires + '; path=/';
		}
	};

	$document.ready(function () {
		cfAdminConditions.init();
	});
})(jQuery);
