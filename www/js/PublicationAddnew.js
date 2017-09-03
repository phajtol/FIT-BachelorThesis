function contains(a, obj) {
	var i = a.length;
	while (i--) {
		if (a[i] === obj) {
			return true;
		}
	}
	return false;
}


function checkValue(value, button) {

	if (!value) {
		button.addClass("disabled");
	} else {
		button.removeClass("disabled");
	}

}


function removeRequired() {
	$('#addnew-volume').removeClass("required");
	$('#addnew-number').removeClass("required");
	$('#addnew-chapter').removeClass("required");
	$('#addnew-pages').removeClass("required");
	$('#addnew-editor').removeClass("required");
	$('#addnew-edition').removeClass("required");
	$('#addnew-booktitle').removeClass("required");
	$('#addnew-school').removeClass("required");
	$('#addnew-institution').removeClass("required");
	$('#addnew-type_of_report').removeClass("required");
	$('#addnew-publisher').removeClass("required");
	$('#addnew-journal').removeClass("required");
	$('#snippet--publicationJournalInfo').removeClass("required");
	$('#addnew-conference').removeClass("required");
	$('#snippet--publicationConferenceInfo').removeClass("required");
	$('#addnew-conferenceYear').removeClass("required");
	$('#snippet--publicationConferenceYearInfo').removeClass("required");
	$('#addnew-isbn').removeClass("required");
	$('#addnew-howpublished').removeClass("required");
	$('#addnew-organization').removeClass("required");
	$('#addnew-authors1').removeClass("required");
	$('#addnew-authors2').removeClass("required");
	$('#addnew-authors3').removeClass("required");
	$('#addnew-address').removeClass("required");
	$('#addnew-note').removeClass("required");
	$('#addnew-url').removeClass("required");
}

function hideAll() {
	$('#addnew-volume').addClass("hidden");
	$('#addnew-number').addClass("hidden");
	$('#addnew-chapter').addClass("hidden");
	$('#addnew-pages').addClass("hidden");
	$('#addnew-editor').addClass("hidden");
	$('#addnew-edition').addClass("hidden");
	$('#addnew-booktitle').addClass("hidden");
	$('#addnew-school').addClass("hidden");
	$('#addnew-institution').addClass("hidden");
	$('#addnew-type_of_report').addClass("hidden");
	$('#addnew-publisher').addClass("hidden");
	$('#addnew-journal').addClass("hidden");
	$('#snippet--publicationJournalInfo').addClass("hidden");
	$('#addnew-conference').addClass("hidden");
	$('#snippet--publicationConferenceInfo').addClass("hidden");
	$('#addnew-conferenceYear').addClass("hidden");
	$('#snippet--publicationConferenceYearInfo').addClass("hidden");
	$('#addnew-isbn').addClass("hidden");
	$('#addnew-howpublished').addClass("hidden");
	$('#addnew-issue_year').addClass("hidden");
	$('#addnew-issue_month').addClass("hidden");
        $('#addnew-organization').addClass("hidden");
	$('#addnew-authors1').addClass("hidden");
	$('#addnew-authors2').addClass("hidden");
	$('#addnew-authors3').addClass("hidden");
	$('#addnew-address').addClass("hidden");
	$('#addnew-note').addClass("hidden");
	$('#addnew-url').addClass("hidden");
}

function showSome(type) {

	switch (type) {
		case "misc":
			$('#addnew-pages').removeClass("hidden");
			$('#addnew-howpublished').removeClass("hidden");
			$('#addnew-authors1').removeClass("hidden");
			$('#addnew-authors2').removeClass("hidden");
			$('#addnew-authors3').removeClass("hidden");
			$('#addnew-issue_year').removeClass("hidden");
			$('#addnew-issue_month').removeClass("hidden");
                        $('#addnew-note').removeClass("hidden");
			$('#addnew-url').removeClass("hidden");
			break;

		case "book":
			$('#addnew-volume').removeClass("hidden");
			$('#addnew-number').removeClass("hidden");
			$('#addnew-pages').removeClass("hidden");
			$('#addnew-authors1').removeClass("hidden");
			$('#addnew-authors2').removeClass("hidden").addClass("required");
			$('#addnew-authors3').removeClass("hidden");
			$('#addnew-editor').removeClass("hidden").addClass("required");
			$('#addnew-publisher').removeClass("hidden").addClass("required");
			$('#addnew-isbn').removeClass("hidden");
			$('#addnew-issue_year').removeClass("hidden");
			$('#addnew-issue_month').removeClass("hidden");
                        $('#addnew-edition').removeClass("hidden");
			$('#addnew-note').removeClass("hidden");
			$('#addnew-url').removeClass("hidden");
			break;

		case "article":
			$('#addnew-volume').removeClass("hidden");
			$('#addnew-number').removeClass("hidden");
			$('#addnew-pages').removeClass("hidden");
			$('#addnew-authors1').removeClass("hidden");
			$('#addnew-authors2').removeClass("hidden").addClass("required");
			$('#addnew-authors3').removeClass("hidden");
			$('#addnew-journal').removeClass("hidden").addClass("required");
			$('#snippet--publicationJournalInfo').removeClass("hidden");
			$('#addnew-issue_year').removeClass("hidden");
			$('#addnew-issue_month').removeClass("hidden");
                        $('#addnew-note').removeClass("hidden");
			$('#addnew-url').removeClass("hidden");
			break;

		case "inproceedings":
			$('#addnew-volume').removeClass("hidden");
			$('#addnew-number').removeClass("hidden");
			$('#addnew-chapter').removeClass("hidden");
			$('#addnew-pages').removeClass("hidden");
			$('#addnew-authors1').removeClass("hidden");
			$('#addnew-authors2').removeClass("hidden").addClass("required");
			$('#addnew-authors3').removeClass("hidden");
			$('#addnew-conference').removeClass("hidden").addClass("required");
			$('#snippet--publicationConferenceInfo').removeClass("hidden");
			$('#addnew-conferenceYear').removeClass("hidden").addClass("required");
			$('#snippet--publicationConferenceYearInfo').removeClass("hidden");
			$('#addnew-isbn').removeClass("hidden");
			$('#addnew-issue_year').removeClass("hidden");
			$('#addnew-issue_month').removeClass("hidden");
                        $('#addnew-organization').removeClass("hidden");
			$('#addnew-note').removeClass("hidden");
			$('#addnew-url').removeClass("hidden");
			break;

		case "proceedings":
			$('#addnew-volume').removeClass("hidden");
			$('#addnew-number').removeClass("hidden");
			$('#addnew-pages').removeClass("hidden");
			$('#addnew-conference').removeClass("hidden").addClass("required");
			$('#snippet--publicationConferenceInfo').removeClass("hidden");
			$('#addnew-conferenceYear').removeClass("hidden").addClass("required");
			$('#snippet--publicationConferenceYearInfo').removeClass("hidden");
			$('#addnew-isbn').removeClass("hidden");
			$('#addnew-issue_year').removeClass("hidden");
                        $('#addnew-issue_month').removeClass("hidden");
			$('#addnew-organization').removeClass("hidden");
			$('#addnew-note').removeClass("hidden");
			$('#addnew-url').removeClass("hidden");
			break;

		case "incollection":
			$('#addnew-booktitle').removeClass("hidden").addClass("required");
			$('#addnew-volume').removeClass("hidden");
			$('#addnew-number').removeClass("hidden");
			$('#addnew-chapter').removeClass("hidden");
			$('#addnew-pages').removeClass("hidden");
			$('#addnew-authors1').removeClass("hidden");
			$('#addnew-authors2').removeClass("hidden").addClass("required");
			$('#addnew-authors3').removeClass("hidden");
			$('#addnew-publisher').removeClass("hidden").addClass("required");
			$('#addnew-isbn').removeClass("hidden");
			$('#addnew-issue_year').removeClass("hidden");
			$('#addnew-issue_month').removeClass("hidden");
                        $('#addnew-edition').removeClass("hidden");
			$('#addnew-note').removeClass("hidden");
			$('#addnew-url').removeClass("hidden");
			break;

		case "inbook":
			$('#addnew-volume').removeClass("hidden");
			$('#addnew-number').removeClass("hidden");
			$('#addnew-chapter').removeClass("hidden").addClass("required");
			$('#addnew-pages').removeClass("hidden").addClass("required");
			$('#addnew-authors1').removeClass("hidden");
			$('#addnew-authors2').removeClass("hidden").addClass("required");
			$('#addnew-authors3').removeClass("hidden");
			$('#addnew-editor').removeClass("hidden").addClass("required");
			$('#addnew-publisher').removeClass("hidden").addClass("required");
			$('#addnew-isbn').removeClass("hidden");
			$('#addnew-issue_year').removeClass("hidden");
			$('#addnew-issue_month').removeClass("hidden");
                        $('#addnew-edition').removeClass("hidden");
			$('#addnew-note').removeClass("hidden");
			$('#addnew-url').removeClass("hidden");
			break;

		case "booklet":
			$('#addnew-pages').removeClass("hidden");
			$('#addnew-howpublished').removeClass("hidden");
			$('#addnew-authors1').removeClass("hidden");
			$('#addnew-authors2').removeClass("hidden");
			$('#addnew-authors3').removeClass("hidden");
			$('#addnew-issue_year').removeClass("hidden");
                        $('#addnew-issue_month').removeClass("hidden");
			$('#addnew-note').removeClass("hidden");
			$('#addnew-url').removeClass("hidden");
			$('#addnew-address').removeClass("hidden");
			break;

		case "manual":
			$('#addnew-pages').removeClass("hidden");
			$('#addnew-authors1').removeClass("hidden");
			$('#addnew-authors2').removeClass("hidden");
			$('#addnew-authors3').removeClass("hidden");
			$('#addnew-issue_year').removeClass("hidden");
                        $('#addnew-issue_month').removeClass("hidden");
			$('#addnew-organization').removeClass("hidden");
			$('#addnew-edition').removeClass("hidden");
			$('#addnew-note').removeClass("hidden");
			$('#addnew-url').removeClass("hidden");
			$('#addnew-address').removeClass("hidden");
			break;

		case "techreport":
			$('#addnew-number').removeClass("hidden");
			$('#addnew-pages').removeClass("hidden");
			$('#addnew-type_of_report').removeClass("hidden");
			$('#addnew-institution').removeClass("hidden").addClass("required");
			$('#addnew-authors1').removeClass("hidden");
			$('#addnew-authors2').removeClass("hidden").addClass("required");
			$('#addnew-authors3').removeClass("hidden");
			$('#addnew-issue_year').removeClass("hidden");
                        $('#addnew-issue_month').removeClass("hidden");
			$('#addnew-note').removeClass("hidden");
			$('#addnew-url').removeClass("hidden");
			$('#addnew-address').removeClass("hidden");
			break;

		case "mastersthesis":
			$('#addnew-pages').removeClass("hidden");
			$('#addnew-authors1').removeClass("hidden");
			$('#addnew-authors2').removeClass("hidden").addClass("required");
			$('#addnew-authors3').removeClass("hidden");
			$('#addnew-issue_year').removeClass("hidden");
                        $('#addnew-issue_month').removeClass("hidden");
			$('#addnew-school').removeClass("hidden").addClass("required");
			$('#addnew-isbn').removeClass("hidden");
			$('#addnew-note').removeClass("hidden");
			$('#addnew-url').removeClass("hidden");
			$('#addnew-address').removeClass("hidden");
			break;

		case "phdthesis":
			$('#addnew-pages').removeClass("hidden");
			$('#addnew-authors1').removeClass("hidden");
			$('#addnew-authors2').removeClass("hidden").addClass("required");
			$('#addnew-authors3').removeClass("hidden");
			$('#addnew-school').removeClass("hidden").addClass("required");
			$('#addnew-issue_year').removeClass("hidden");
                        $('#addnew-issue_month').removeClass("hidden");
			$('#addnew-note').removeClass("hidden");
			$('#addnew-url').removeClass("hidden");
			$('#addnew-address').removeClass("hidden");
			break;

		case "unpublished":
			$('#addnew-pages').removeClass("hidden");
			$('#addnew-authors1').removeClass("hidden");
			$('#addnew-authors2').removeClass("hidden").addClass("required");
			$('#addnew-authors3').removeClass("hidden");
			$('#addnew-issue_year').removeClass("hidden");
                        $('#addnew-issue_month').removeClass("hidden");
			$('#addnew-note').removeClass("hidden").addClass("required");
			$('#addnew-url').removeClass("hidden");
			break;

	}
}

$(document).ready(function() {

	$(function() {
		$('<div class="windows8" id="ajax-spinner"><div class="wBall" id="wBall_1"><div class="wInnerBall"></div></div><div class="wBall" id="wBall_2"><div class="wInnerBall"></div></div><div class="wBall" id="wBall_3"><div class="wInnerBall"></div></div><div class="wBall" id="wBall_4"><div class="wInnerBall"></div></div><div class="wBall" id="wBall_5"><div class="wInnerBall"></div></div></div>').appendTo("html").ajaxStop(function() {
			$(this).hide().css({
				position: "fixed",
				left: "50%",
				top: "50%"
			});
		}).hide();

	});

	$('#frm-publicationAddNewForm-pub_type').on('change', function(event) {
		hideAll();
		removeRequired();
		showSome($(this).val());
	});

	showSome($('#frm-publicationAddNewForm-pub_type').val());




	Nette.validators.PublicationFormRules_validateJournal_IsRequired = function(elem, arg, value) {
		var requiredTypes = [
			'article'
		];
		if (contains(requiredTypes, $('#frm-publicationAddNewForm-pub_type').val()) && !value) {
			return false;
		}
		return true;
	};

	Nette.validators.PublicationFormRules_validateIssueDate_IsRequired = function(elem, arg, value) {
		var requiredTypes = [
			'book',
			'article',
			'inproceedings',
			'proceedings',
			'incollection',
			'inbook',
			'techreport',
			'mastersthesis',
			'phdthesis'
		];
		if (contains(requiredTypes, $('#frm-publicationAddNewForm-pub_type').val()) && !value) {
			return false;
		}
		return true;
	};

	Nette.validators.PublicationFormRules_validateAuthor_IsOptional = function(elem, arg, value) {
		var requiredTypes = [
			'book',
			'inbook'
		];
		if (contains(requiredTypes, $('#frm-publicationAddNewForm-pub_type').val()) && !value && !$('#frm-publicationAddNewForm-editor').val()) {
			return false;
		}
		return true;
	};

	Nette.validators.PublicationFormRules_validateAuthor_IsRequired = function(elem, arg, value) {
		var requiredTypes = [
			'article',
			'inproceedings',
			'incollection',
			'techreport',
			'mastersthesis',
			'phdthesis',
			'unpublished'
		];
		if (contains(requiredTypes, $('#frm-publicationAddNewForm-pub_type').val()) && !value) {
			return false;
		}
		return true;
	};

	Nette.validators.PublicationFormRules_validateEditor_IsOptional = function(elem, arg, value) {
		var requiredTypes = [
			'book',
			'inbook'
		]; // upravit authors, na selected asi, ptz nejsou nakopceny v poli
		if (contains(requiredTypes, $('#frm-publicationAddNewForm-pub_type').val()) && !value && !$('#frm-publicationAddNewForm-authors').val()) {
			return false;
		}
		return true;
	};

	Nette.validators.PublicationFormRules_validateChapter_IsOptional = function(elem, arg, value) {
		var requiredTypes = [
			'inbook'
		];
		if (contains(requiredTypes, $('#frm-publicationAddNewForm-pub_type').val()) && !value && !$('#frm-publicationAddNewForm-pages').val()) {
			return false;
		}
		return true;
	};

	Nette.validators.PublicationFormRules_validatePages_IsOptional = function(elem, arg, value) {
		var requiredTypes = [
			'inbook'
		];
		if (contains(requiredTypes, $('#frm-publicationAddNewForm-pub_type').val()) && !value && !$('#frm-publicationAddNewForm-chapter').val()) {
			return false;
		}
		return true;
	};

	Nette.validators.PublicationFormRules_validateBooktitle_IsRequired = function(elem, arg, value) {
		var requiredTypes = [
			'incollection'
		];
		if (contains(requiredTypes, $('#frm-publicationAddNewForm-pub_type').val()) && !value) {
			return false;
		}
		return true;
	};

	Nette.validators.PublicationFormRules_validateSchool_IsRequired = function(elem, arg, value) {
		var requiredTypes = [
			'mastersthesis',
			'phdthesis'
		];
		if (contains(requiredTypes, $('#frm-publicationAddNewForm-pub_type').val()) && !value) {
			return false;
		}
		return true;
	};

	Nette.validators.PublicationFormRules_validateInstitution_IsRequired = function(elem, arg, value) {
		var requiredTypes = [
			'techreport'
		];
		if (contains(requiredTypes, $('#frm-publicationAddNewForm-pub_type').val()) && !value) {
			return false;
		}
		return true;
	};

	Nette.validators.PublicationFormRules_validatePublisher_IsRequired = function(elem, arg, value) {
		var requiredTypes = [
			'book',
			'incollection',
			'inbook'
		];
		if (contains(requiredTypes, $('#frm-publicationAddNewForm-pub_type').val()) && !value) {
			return false;
		}
		return true;
	};

	Nette.validators.PublicationFormRules_validateConference_IsRequired = function(elem, arg, value) {
		var requiredTypes = [
			'inproceedings',
			'proceedings'
		];
		if (contains(requiredTypes, $('#frm-publicationAddNewForm-pub_type').val()) && !value) {
			return false;
		}
		return true;
	};

	Nette.validators.PublicationFormRules_validateConferenceYear_IsRequired = function(elem, arg, value) {
		var requiredTypes = [
			'inproceedings',
			'proceedings'
		];
		if (contains(requiredTypes, $('#frm-publicationAddNewForm-pub_type').val()) && !value) {
			return false;
		}
		return true;
	};

	Nette.validators.PublicationFormRules_validateNote_IsRequired = function(elem, arg, value) {
		var requiredTypes = [
			'unpublished'
		];
		if (contains(requiredTypes, $('#frm-publicationAddNewForm-pub_type').val()) && !value) {
			return false;
		}
		return true;
	};

});