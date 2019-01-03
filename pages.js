var fields = {
    report_id: null,
    report_type: null,
    report_subtype: null,
    report_name: null,
    master: null,
    proceeding_category: [],
    proceeding_type: [],
    proceeding_subtype: [],
    case_tags: [],
    claim_type: [],
    hearing_type: [],
    hearing_subtype: [],
    hearing_mode: [],
    party_type: [],
    outcome: [],
    individual_company: 2,
    self: 2,
    appeals: 2,
    court: 2,
    practice_area: 2,
    judge: null,
    start_date: null,
    end_date: null,
    min_date: null,
    max_date: null,
    start_hear: null,
    end_hear: null
};

// ---------------------------

// Global selectors
var gl = {
  reportList: '#reports-list',
  reports: '#reports',
  overlay: '#report-overlay',
  court: '.global-court',
  practice_area: '.global-practice-area',
  datePicker: '.global-date-range',
  refine: '#refine',
  close: '#close',
  defCourt: '#default-court',
  defPrArea: '#default-practice-area',
  defDatePicker: '#default-date-range',
  defSettings: '#default-settings',
  preLoader: '#pre-loader',
  run: '.run-report',
  runLine: '.run-line-report',
  reportContainer: '#report-container',
  rightPanel: '.right-panel',
  rightPanelContent: '.right-panel-content',
  resultWrapper: '#result-wrapper',
  sentence: '#sentence',
  section: '.report-section',
  refresh: '.refresh-report',
  settings: '.global-report-settings',
};

// Case Law selectors
var clTitle = 'Find the best case law that matches your criteria';
var orTitle = 'See win/loss breakdowns for motions and trials';
var dtTitle = 'Average decision publication time by judge or master';
var icTitle = 'Personal Injury Costs Report';

var cl = {
  result: '#cl-result',
  court: '#cl-court',
  practice_area: '#cl-practice-area',
  datePicker: '#cl-date-range',
  hearRange: '#cl-hear-date-range',
  proceeding_type: '#cl-proceeding-type',
  case_category: '#cl-case-category',
  case_tags: '#cl-case-tags',
  claim_type: '#cl-claim-type',
  hearings: '#cl-hearings',
  hearing_mode: '#cl-hearing-mode',
  party_type: '#cl-party-type',
  individual_company: '#cl-company-individual',
  judge: '#cl-judge',
  outcome: '#cl-outcome',
  run_default: '.run-cl-default',
  refresh_report: '#cl-refresh-report',
  reset_report: '#cl-reset-report',
  appeals: '#cl-appeals',
  appeals_checked: 'input[name="cl-appeals"]:checked',
  self: '#cl-self',
  self_checked: 'input[name="cl-self"]:checked',

  defaultValue: {
    practice_area: 2,
    court: 2,
    party: 2,
    proceeding_type: [],
    case_category: [],
    case_tags: [],
    hearings: [],
    claim_type: [],
    hearing_type: [],
    hearing_subtype: [],
    hearing_mode: [],
    party_type: [],
    individual_company: 2,
    outcome: [],
    self: 2,
    appeals: 2,
    judge: []
  }
};

// Outcome report selectors
var or = {
  result: '#or-result',
  court: '#or-court',
  practice_area: '#or-practice-area',
  datePicker: '#or-date-range',
  hearRange: '#or-hear-date-range',
  proceeding_type: '#or-proceeding-type',
  case_category: '#or-case-category',
  case_tags: '#or-case-tags',
  claim_type: '#or-claim-type',
  hearings: '#or-hearings',
  party_type: '#or-party-type',
  individual_company: '#or-company-individual',
  judge: '#or-judge',
  hearing_mode: '#or-hearing-mode',
  run_default: '.run-or-default',
  refresh: '#or-refresh',
  refresh_report: '#or-refresh-report',
  reset_report: '#or-reset-report',
  moving_party: '#or-moving-party',
  appeals: '#or-appeals',
  appeals_checked: 'input[name="or-appeals"]:checked',
  self: '#or-self',
  self_checked: 'input[name="or-self"]:checked',

  defaultValue: {
    practice_area: 2,
    court: 2,
    party: 2,
    proceeding_type: [],
    case_category: [],
    case_tags: [],
    hearings: [],
    claim_type: [],
    hearing_type: [],
    hearing_subtype: [],
    hearing_mode: [],
    party_type: 0,
    individual_company: 2,
    outcome: [],
    self: 2,
    appeals: 2,
    judge: [],
    hearing_decision: 0,
    moving_party: 1
  }
};

// Dec Tat selectors
var dt = {
  result: '#dt-result',
  court: '#dt-court',
  practice_area: '#dt-practice-area',
  datePicker: '#dt-date-range',
  hearRange: '#dt-hear-date-range',
  proceeding_type: '#dt-proceeding-type',
  case_category: '#dt-case-category',
  case_tags: '#dt-case-tags',
  claim_type: '#dt-claim-type',
  hearings: '#dt-hearings',
  hearing_mode: '#dt-hearing-mode',
  party_type: '#dt-party-type',
  individual_company: '#dt-company-individual',
  judge: '#dt-judge',
  outcome: '#dt-outcome',
  run_default: '.run-dt-default',
  refresh_report: '#dt-refresh-report',
  reset_report: '#dt-reset-report',
  appeals: '#dt-appeals',
  appeals_checked: 'input[name="dt-appeals"]:checked',
  self: '#dt-self',
  self_checked: 'input[name="dt-self"]:checked',

  defaultValue: {
    practice_area: 2,
    court: 2,
    party: 2,
    proceeding_type: [],
    case_category: [],
    case_tags: [],
    hearings: [],
    claim_type: [],
    hearing_type: [],
    hearing_subtype: [],
    hearing_mode: [],
    party_type: [],
    individual_company: 2,
    outcome: [],
    self: 2,
    appeals: 2,
    judge: [0]
  }
};

// Hearing Analytics selectors
var ha = {
  court: '#ha-court',
  practice_area: '#ha-practice-area',
  hearings: '#ha-hearings'
}

// Personal Injury Costs Report
var ic = {
  result: '#ic-result',
  court: '#ic-court',
  practice_area: '#ic-practice-area',
  datePicker: '#ic-date-range',
  hearRange: '#ic-hear-date-range',
  proceeding_type: '#ic-proceeding-type',
  case_category: '#ic-case-category',
  case_tags: '#ic-case-tags',
  claim_type: '#ic-claim-type',
  hearings: '#ic-hearings',
  hearing_mode: '#ic-hearing-mode',
  party_type: '#ic-party-type',
  individual_company: '#ic-company-individual',
  judge: '#ic-judge',
  outcome: '#ic-outcome',
  run_default: '.run-ic-default',
  refresh_report: '#ic-refresh-report',
  reset_report: '#ic-reset-report',
  appeals: '#ic-appeals',
  appeals_checked: 'input[name="ic-appeals"]:checked',
  self: '#ic-self',
  self_checked: 'input[name="ic-self"]:checked',

  defaultValue: {
    practice_area: 2,
    court: 2,
    party: 2,
    proceeding_type: [],
    case_category: [],
    case_tags: [],
    hearings: [],
    claim_type: [],
    hearing_type: [],
    hearing_subtype: [],
    hearing_mode: [],
    party_type: [],
    individual_company: 2,
    outcome: [],
    self: 2,
    appeals: 2,
    judge: []
  }
};

// SerfSelect options
var noNested = {noNested: true, startCollapsed: false};
var single = {single: true, startCollapsed: false};
var multiple = {multiple: true, startCollapsed: true};
var collapsed = {noNested: false, startCollapsed: true};
var nested = {};

$(document).ready(function () {
  var body = $('body');

  // ======== Report actions =======

  // ------ Refresh Reports -------

  // Refresh Case Law
  body.delegate(cl.refresh_report, 'click', function (e) {
    var resCheck = checkGlobalMandatoryFields({
      court: cl.court,
      practice_area: cl.practice_area
    });

    if (!resCheck) return false;

    if (!checkMandatoryFields()) {
      return false;
    }

    if (filterData(cl.outcome) && !filterData(cl.party_type)) {
      confirmOutcomeWithoutPartyType(prepareCaseLaw);
    } else {
      prepareCaseLaw();
    }
  });

  // Refresh Outcome Report
  body.delegate(or.refresh_report, 'click', function (e) {
    var resCheck = checkGlobalMandatoryFields({
      court: or.court,
      practice_area: or.practice_area
    });

    if (!resCheck) return false;

    if (!checkMandatoryFields()) {
      return false;
    }

    // Check for Outcome without Party Type
    if (filterData(or.outcome) && !filterData(or.party_type)) {
      confirmOutcomeWithoutPartyType(prepareOutcome);
    } else {
      prepareOutcome();
    }
  });

  // Run refresh Outcome Report from the result page
  body.delegate(or.refresh, 'click', function (e) {
    prepareOutcome();
  });

  // Refresh Dec Tat
  body.delegate(dt.refresh_report, 'click', function (e) {
    var resCheck = checkGlobalMandatoryFields({
      court: dt.court,
      practice_area: dt.practice_area,
      judge: dt.judge
    });

    if (!resCheck) return false;

    if (!checkMandatoryFields()) {
      return false;
    }

    if (filterData(dt.outcome) && !filterData(dt.party_type)) {
      confirmOutcomeWithoutPartyType(prepareDecTat);
    } else {
      prepareDecTat();
    }
  });

  // Refresh Personal Injury Costs Report
  body.delegate(ic.refresh_report, 'click', function (e) {
    var resCheck = checkGlobalMandatoryFields({
      court: ic.court,
      practice_area: ic.practice_area
    });

    if (!resCheck) return false;

    if (!checkMandatoryFields()) {
      return false;
    }

    if (filterData(ic.outcome) && !filterData(ic.party_type)) {
      confirmOutcomeWithoutPartyType(prepareInjuryCosts);
    } else {
      prepareInjuryCosts();
    }
  });

  // ------ Reset Reports -------

  // Reset Case Law
  body.delegate(cl.reset_report, 'click', function (e) {
    e.preventDefault();

    confirmReportReset(resetCaseLawCallback);
  });

  // Reset Outcome Report
  body.delegate(or.reset_report, 'click', function (e) {
    e.preventDefault();

    confirmReportReset(resetOutcomeCallback);
  });

  // Reset Dec Tat
  body.delegate(dt.reset_report, 'click', function (e) {
    e.preventDefault();

    confirmReportReset(resetDecTatCallback);
  });

  // Reset Case Law
  body.delegate(ic.reset_report, 'click', function (e) {
    e.preventDefault();

    confirmReportReset(resetInjuryCostsCallback);
  });

  function resetCaseLawCallback() {
    allowUpdateFields = 0;

    resetCaseLaw();
    renderCaseLawSelects();
    resetDatePicker(cl.hearRange);

    // ---------------

    // Use default settings
    if ($(gl.defSettings).val() == 1) {
     
      copyOptions($(gl.defCourt), $(cl.court));
      copySerfSelectOptions(gl.defPrArea, cl.practice_area);
      copyDateFromDatePicker(gl.defDatePicker, cl.datePicker, getDateFormat());
    } else {
      resetDatePicker(cl.datePicker);
    }

    allowUpdateFields = 1;
  }

  function resetOutcomeCallback() {
    allowUpdateFields = 0;

    resetOutcome();
    renderOutcomeSelects();
    resetDatePicker(or.hearRange);

    // ---------------

    if ($(gl.defSettings).val() == 1) {
      // copySerfSelectOptions(gl.defCourt, or.court);
      copyOptions($(gl.defCourt), $(or.court));
      copySerfSelectOptions(gl.defPrArea, or.practice_area);
      copyDateFromDatePicker(gl.defDatePicker, or.datePicker, getDateFormat());
    } else {
      resetDatePicker(or.datePicker);
    }

    allowUpdateFields = 1;
  }

  function resetDecTatCallback() {
    allowUpdateFields = 0;

    resetDecTat();
    renderDecTatSelects();
    resetDatePicker(dt.hearRange);

    // ---------------

    if ($(gl.defSettings).val() == 1) {
      // copySerfSelectOptions(gl.defCourt, dt.court);
      copyOptions($(gl.defCourt), $(dt.court));
      copySerfSelectOptions(gl.defPrArea, dt.practice_area);
      copyDateFromDatePicker(gl.defDatePicker, dt.datePicker, getDateFormat());
    } else {
      resetDatePicker(dt.datePicker);
    }

    allowUpdateFields = 1;
  }

  function resetInjuryCostsCallback() {
    allowUpdateFields = 0;

    resetInjuryCosts();
    renderInjuryCostsSelects();
    resetDatePicker(ic.hearRange);

    // ---------------

    // Use default settings
    if ($(gl.defSettings).val() == 1) {
      copyOptions($(gl.defCourt), $(ic.court));
      copySerfSelectOptions(gl.defPrArea, ic.practice_area);
      copyDateFromDatePicker(gl.defDatePicker, ic.datePicker, getDateFormat());
    } else {
      resetDatePicker(ic.datePicker);
    }

    allowUpdateFields = 1;
  }

  // ======== Events =======

  // Update Case Law Search settings
  body.delegate('select' + cl.court + ',' + 'select' + cl.practice_area, 'change', function () {
    updateGlobalReportSettings({
      inputRole: $(this).attr('data-role'),
      court: cl.court,
      practice_area: cl.practice_area,
      master: fields.master,
      report_id: fields.report_id,
      datePicker: cl.datePicker
    });
  });

  // Update Hearing Outcome settings
  body.delegate('select' + or.court + ',' + 'select' + or.practice_area, 'change', function () {
    updateGlobalReportSettings({
      inputRole: $(this).attr('data-role'),
      court: or.court,
      practice_area: or.practice_area,
      master: fields.master,
      report_id: fields.report_id,
      datePicker: or.datePicker
    });
  });

  // Update Dec TAT settings
  body.delegate('select' + dt.court + ',' + 'select' + dt.practice_area, 'change', function () {
    updateGlobalReportSettings({
      inputRole: $(this).attr('data-role'),
      court: dt.court,
      practice_area: dt.practice_area,
      master: fields.master,
      report_id: fields.report_id,
      datePicker: dt.datePicker
    });
  });

  // Update Hearing Analytics settings
  body.delegate('select' + ha.court + ',' + 'select' + ha.practice_area, 'change', function () {
    updateGlobalReportSettings({
      inputRole: $(this).attr('data-role'),
      court: ha.court,
      practice_area: ha.practice_area,
      hearings: {selector: ha.hearings, single: true},
      master: fields.master,
      report_id: fields.report_id
    });
  });

  // Update Personal Injury Costs Report settings
  body.delegate('select' + ic.court + ',' + 'select' + ic.practice_area, 'change', function () {
    updateGlobalReportSettings({
      inputRole: $(this).attr('data-role'),
      court: ic.court,
      practice_area: ic.practice_area,
      master: fields.master,
      report_id: fields.report_id,
      datePicker: ic.datePicker
    });
  });

  // ---- Practice Area ----

  // Practice area changing in Case Law
  body.delegate('select' + cl.practice_area, 'change', function () {
    var prArea = $(this).val();

    setTimeout(function () {
      setGeneralReportSettings('cl');
      getCaseCategory(cl.case_category, prArea);
      getHearingDateRange(cl.hearRange, fields);
    }, 1000);
  });

  // Practice area changing in Outcome Report
  body.delegate('select' + or.practice_area, 'change', function () {
    var prArea = $(this).val();

    setTimeout(function () {
      setGeneralReportSettings('or');
      getCaseCategory(or.case_category, prArea);
      getHearingDateRange(or.hearRange, fields);
    }, 1000);
  });

  // Practice area changing in Dec Tat
  body.delegate('select' + dt.practice_area, 'change', function () {
    var prArea = $(this).val();

    setTimeout(function () {
      setGeneralReportSettings('dt');
      getCaseCategory(dt.case_category, prArea);
      getHearingDateRange(dt.hearRange, fields);
    }, 1000);
  });

  // Practice area changing in Personal Injury Costs Report
  body.delegate('select' + ic.practice_area, 'change', function () {
    var prArea = $(this).val();

    setTimeout(function () {
      setGeneralReportSettings('ic');
      getCaseCategory(ic.case_category, prArea);
      getHearingDateRange(ic.hearRange, fields);
    }, 1000);
  });

  // ------- Court -------

  // Court changing in Case law
  body.delegate('select' + cl.court, 'change', function () {
    var court = $(this).val();

    getHearings(cl.hearings, [], court, true);
    getProceedingType(cl.proceeding_type, court, true);
    getPartyType(cl.party_type, court, true);
  });

  // Court changing in Outcome
  body.delegate('select' + or.court, 'change', function () {
    var court = $(this).val();

    getHearings(or.hearings, [], court, true);
    getProceedingType(or.proceeding_type, court, true);
    getPartyType(or.party_type, court, true);
  });

  // Court changing in Dec tat
  body.delegate('select' + dt.court, 'change', function () {
    var court = $(this).val();

    getHearings(dt.hearings, [], court, true);
    getProceedingType(dt.proceeding_type, court, true);
    getPartyType(dt.party_type, court, true);
  });

  // Court changing in Personal Injury Costs Report
  body.delegate('select' + ic.court, 'change', function () {
    var court = $(this).val();

    getHearings(ic.hearings, [], court, true);
    getProceedingType(ic.proceeding_type, court, true);
    getPartyType(ic.party_type, court, true);
  });

  // --- Outcome Report specific events ---

  // Don't show "Moving or Responding Party" as available option for Moving Party
  body.delegate('select' + or.party_type, 'change', function () {
    checkMovingParty($(this), true);
  });

  // Don't show "Any party" as available option for Party Type
  body.delegate('select' + or.moving_party, 'change', function () {
    var anyParty = $(or.party_type).next('.serfSelect').find('.selections .item[data-value="0"]');

    if ($(this).val() == 2) {
      anyParty.css('display', 'none');
    } else {
      anyParty.css('display', 'block');
    }
  });

  // ======== Dynamic reports =======

  // Initial report running
  $(gl.run + ',' + gl.runLine).click(function () {
    var trigger = $(this);

    if (trigger.hasClass('disabled')) {
      return false;
    }

    trigger.addClass('disabled');

    if (trigger.hasClass('run-report')) {
      var report = trigger;
    } else {
      report = trigger.closest('.report-line')
    }

    var reportId = +report.attr('data-report');
    var type = report.attr('data-type');
    var master = report.attr('data-master');

    if (reportId > 0) {
      var section = report.closest(gl.section);
      var settings = section.find(gl.settings);

      if (settings.length) {
        var court = section.find(gl.court);
        var prArea = section.find(gl.practice_area);
        var date = section.find(gl.datePicker);

        var dates = getDatePickerDates(date);

        fields.court = court.val();
        fields.practice_area = prArea.val();
        fields.start_date = dates.start;
        fields.end_date = dates.end;
        fields.min_date = dates.min;
        fields.max_date = dates.max;
      }

      fields.report_id = reportId;
      fields.master = master ? master : 0;
      fields.report_name = report.attr('data-name');

      // Skip report fields filling
      if (type == 'ic') {
        initialInjuryCosts();
        return false;
      }

      // Get right panel
      $.get('/report/get-report-fields', fields, function (response) {
        if (response.error == undefined) {
          // Fill right panel with fields & their options
          var sidebar = $(gl.rightPanelContent);
          sidebar.html(response);

          // -----------

          // Check default report values before running
          if (settings.length) {
            var selector = '#' + type + '-';
            var defaultCourt = sidebar.find(selector + 'court').attr('data-default');
            var defaultPrArea = sidebar.find(selector + 'practice-area').attr('data-default');

            if (defaultCourt != 1 || defaultPrArea != 1) {
              var resCheck = checkGlobalMandatoryFields({
                court: court,
                practice_area: prArea
              });

              if (!resCheck) return false;
            }
          }

          // -----------

          if (type == 'cl') {
            initialCaseLaw(court, prArea, date);
          } else if (type == 'or') {
            initialOutcomeReport(court, prArea, date);
          } else if (type == 'dt') {
            initialDecTat(court, prArea, date);
          } else if (type == 'ha') {
            initialHearingAnalytics(court, prArea, date);
          } else if (type == 'ic') {
            initialInjuryCosts(court, prArea, date);
          }
        }
      });
    }
  });

  // ======== Reports =======

  function initialCaseLaw(court, prArea, date) {
    copyOptions(court, $(cl.court));
    copySerfSelectOptions(prArea, cl.practice_area);

    checkDefaultCaseLawValues();

    fields.names = getFieldValueNames($(gl.rightPanelContent));

    runCaseLaw(true);

    copyDateFromDatePicker(date, cl.datePicker);
    getJudges(cl.judge, fields.court, $(cl.judge).val(), false, true);
  }

  function initialOutcomeReport(court, prArea, date) {
    copyOptions(court, $(or.court));
    copySerfSelectOptions(prArea, or.practice_area);

    checkDefaultOutcomeValues();

    runOutcome(true);

    copyDateFromDatePicker(date, or.datePicker);
    getJudges(or.judge, fields.court, $(or.judge).val(), false, true);
  }

  function initialDecTat(court, prArea, date) {
    copyOptions(court, $(dt.court));
    copySerfSelectOptions(prArea, dt.practice_area);

    checkDefaultDecTatValues();

    runDecTat(true);

    copyDateFromDatePicker(date, dt.datePicker);
    getJudges(dt.judge, fields.court, $(dt.judge).val(), true, true);
  }

  function initialHearingAnalytics(court, prArea, date) {
    window.location.href = '/hearing-analytics';
  }

  function initialInjuryCosts(court, prArea, date) {
    copyOptions(court, $(ic.court));
    copySerfSelectOptions(prArea, ic.practice_area);

    checkDefaultInjuryCostsValues();

    runInjuryCosts(true);

    copyDateFromDatePicker(date, ic.datePicker);
    getJudges(ic.judge, fields.court, $(ic.judge).val(), false, true);
  }

  // Check whether the default Case Law values are set and if so - use them
  function checkDefaultCaseLawValues() {
    defaultProceedingCategory(cl);
    defaultProceedingType(cl);
    defaultProceedingSubType(cl);
    defaultCaseTags(cl);
    defaultClaimType(cl);
    defaultHearingType(cl);
    defaultHearingSubType(cl);
    defaultHearingMode(cl);
    defaultPartyType(cl);
    defaultOutcome(cl);
    defaultCourt(cl);
    defaultPrArea(cl);
    defaultJudge(cl);
    defaultSelf(cl);
    defaultAppeals(cl);
    defaultIndCompany(cl);
    defaultDateRange(cl);
  }

  // Check whether the default Outcome values are set and if so - use them
  function checkDefaultOutcomeValues() {
    defaultProceedingCategory(or);
    defaultProceedingType(or);
    defaultProceedingSubType(or);
    defaultCaseTags(or);
    defaultClaimType(or);
    defaultHearingType(or);
    defaultHearingSubType(or);
    defaultHearingMode(or);
    defaultPartyType(or);
    defaultCourt(or);
    defaultPrArea(or);
    defaultJudge(or);
    defaultSelf(or);
    defaultAppeals(or);
    defaultIndCompany(or);
    defaultDateRange(or);

    var movingPartyVal = $(or.moving_party).val();
    if (movingPartyVal) {
      fields.moving_party = movingPartyVal;
    } else {
      fields.moving_party = 1;
    }
  }

  // Check whether the default Dec Tat values are set and if so - use them
  function checkDefaultDecTatValues() {
    defaultProceedingCategory(dt);
    defaultProceedingType(dt);
    defaultProceedingSubType(dt);
    defaultCaseTags(dt);
    defaultClaimType(dt);
    defaultHearingType(dt);
    defaultHearingSubType(dt);
    defaultHearingMode(dt);
    defaultPartyType(dt);
    defaultOutcome(dt);
    defaultCourt(dt);
    defaultPrArea(dt);
    defaultJudge(dt);
    defaultSelf(dt);
    defaultAppeals(dt);
    defaultIndCompany(dt);
    defaultDateRange(dt);
  }

  // Check whether the default Case Law values are set and if so - use them
  function checkDefaultInjuryCostsValues() {
    defaultProceedingCategory(ic);
    defaultProceedingType(ic);
    defaultProceedingSubType(ic);
    defaultCaseTags(ic);
    defaultClaimType(ic);
    defaultHearingType(ic);
    defaultHearingSubType(ic);
    defaultHearingMode(ic);
    defaultPartyType(ic);
    defaultOutcome(ic);
    defaultCourt(ic);
    defaultPrArea(ic);
    defaultJudge(ic);
    defaultSelf(ic);
    defaultAppeals(ic);
    defaultIndCompany(ic);
    defaultDateRange(ic);
  }

  // Case Law Search
  function prepareCaseLaw(firstRun, defaultSettings) {
    if (firstRun == undefined || firstRun !== true) {
      firstRun = false;
    }

    if (defaultSettings == undefined || defaultSettings !== true) {
      defaultSettings = false;
    }

    testReportFields('before run');

    // --------------------

    setReportFields('cl', firstRun, defaultSettings);

    testReportFields('run');

    // Run Report
    runCaseLaw(firstRun);
  }

  function runCaseLaw(firstRun) {
    $(gl.preLoader).fadeIn(100);

    $.post('/report/case-law', fields, function (response) {
      if (response.error != undefined) {
        document.location.href = '/report';
      }

      if (firstRun) {
        $(gl.sentence).html('');

        renderCaseLawSelects();

        getHearingDateRange(cl.hearRange, fields);

        testReportFields('complete');

        setFieldEvents('cl');
      }

      // 'Case Law Search'
      changeTitle(fields.report_name, clTitle);

      renderReport(response);
    });
  }

  // Outcome Report
  function prepareOutcome(firstRun, defaultSettings) {
    if (firstRun == undefined || firstRun !== true) {
      firstRun = false;
    }

    if (defaultSettings == undefined || defaultSettings !== true) {
      defaultSettings = false;
    }

    testReportFields('before run');

    setReportFields('or', firstRun, defaultSettings);

    testReportFields('run');

    runOutcome(firstRun);
  }

  function runOutcome(firstRun) {
    $(gl.preLoader).fadeIn(100);

    $.post('/report/outcome-report', fields, function (response) {
      if (response.error != undefined) {
        document.location.href = '/report';
      }

      if (firstRun) {
        $.post('/report/get-outcome-report-sentence', {court: fields.court}, function (resp) {
          // Render Outcome Report sentence
          $(gl.sentence).html(resp);

          renderOutcomeSelects();

          getHearingDateRange(or.hearRange, fields);

          testReportFields('complete');

          $(or.party_type).trigger('change');

          setFieldEvents('or');
        })
      }

      renderReport(response);

      // -------------

      // 'Outcome Report'
      changeTitle(fields.report_name, orTitle);

      makeGraph('hearing-outcome-chart', getOutcomeGraphOptions());
    });
  }

  // Decision Turnaround Time
  function prepareDecTat(firstRun, defaultSettings) {
    if (firstRun == undefined || firstRun !== true) {
      firstRun = false;
    }

    if (defaultSettings == undefined || defaultSettings !== true) {
      defaultSettings = false;
    }

    testReportFields('before run');

    // --------------

    setReportFields('dt', firstRun, defaultSettings);

    testReportFields('run');

    runDecTat(firstRun);
  }

  function runDecTat(firstRun) {
    $(gl.preLoader).fadeIn(100);

    if (!fields.judge || fields.judge == 0) { // All judges
      fields.report_type = 2;

      $.post('/report/dec-tat', fields).done(function (response) {
        decTatResponseHandler(response, firstRun);

        var ranges = $('#dec-tat-ranges').val();
        if (ranges.length > 0) {
          ranges = JSON.parse(ranges);

          makeGraph('dec-tat-chart', getDecTatGraphOptions(ranges));
        }
      });

    } else { // Single judge
      fields.report_type = 1;

      $.post('/report/dec-tat-decisions', fields).done(function (response) {
        decTatResponseHandler(response, firstRun);
      });
    }
  }

  // Case Law Search
  function prepareInjuryCosts(firstRun, defaultSettings) {
    if (firstRun == undefined || firstRun !== true) {
      firstRun = false;
    }

    if (defaultSettings == undefined || defaultSettings !== true) {
      defaultSettings = false;
    }

    testReportFields('before run');

    // --------------------

    if (firstRun && !defaultSettings) {
      var dateRange = $(gl.datePicker);
    } else {
      dateRange = $(ic.datePicker);
    }

    var dates = getDatePickerDates(dateRange);
    var hearDates = getDatePickerDates(ic.hearRange);

    fields.proceeding_category = getProceedingCategoryValue(ic);
    fields.proceeding_type = getProceedingTypeValue(ic);
    fields.proceeding_subtype = getProceedingSubTypeValue(ic);
    fields.case_tags = getCaseTagsValue(ic);
    fields.claim_type = getClaimTypeValue(ic);
    fields.hearing_type = getHearingTypeValue(ic);
    fields.hearing_subtype = getHearingSubTypeValue(ic);
    fields.hearing_mode = getHearingModeValue(ic);
    fields.party_type = getPartyTypeValue(ic);
    fields.individual_company = getIndividualCompanyValue(ic);
    fields.outcome = getOutcomeValue(ic);
    fields.self = getSelfValue(ic);
    fields.appeals = getAppealsValue(ic);
    fields.court = (defaultSettings) ? $(gl.defCourt).val() : $(ic.court).val();
    fields.practice_area = (defaultSettings) ? $(gl.defPrArea).val() : $(ic.practice_area).val();
    fields.judge = filterData(ic.judge);
    fields.start_date = dates.start;
    fields.end_date = dates.end;
    fields.min_date = dates.min;
    fields.max_date = dates.max;
    fields.start_hear = hearDates.start;
    fields.end_hear = hearDates.end;

    fields = checkIncorrectValues(fields);

    testReportFields('run');

    // Run Report
    runInjuryCosts(firstRun);
  }

  function runInjuryCosts(firstRun) {
    $(gl.preLoader).fadeIn(100);

    $.post('/report/injury-costs', fields, function (response) {
      if (response.error != undefined) {
        document.location.href = '/report';
      }

      if (firstRun) {
        $(gl.sentence).html('');

        renderInjuryCostsSelects();

        getHearingDateRange(ic.hearRange, fields);

        testReportFields('complete');

        setFieldEvents('ic');
      }

      changeTitle(fields.report_name, icTitle);

      renderReport(response);
    });
  }

  // ---------------------

  function defaultProceedingCategory(group) {
    var val = getProceedingCategoryValue(group);
    if (val && val != '') {
      logDefaultFields('proceeding_category', fields.proceeding_category, val);
      fields.proceeding_category = val;
    }
  }

  function defaultProceedingType(group) {
    var val = getProceedingTypeValue(group);
    if (val && val != '') {
      logDefaultFields('proceeding_type', fields.proceeding_type, val);
      fields.proceeding_type = val;
    }
  }

  function defaultProceedingSubType(group) {
    var val = getProceedingSubTypeValue(group);
    if (val && val != '') {
      logDefaultFields('proceeding_subtype', fields.proceeding_subtype, val);
      fields.proceeding_subtype = val;
    }
  }

  function defaultCaseTags(group) {
    var val = getCaseTagsValue(group);
    if (val && val != '') {
      logDefaultFields('case_tags', fields.case_tags, val);
      fields.case_tags = val;
    }
  }

  function defaultClaimType(group) {
    var val = getClaimTypeValue(group);
    if (val && val != '') {
      logDefaultFields('claim_type', fields.claim_type, val);
      fields.claim_type = val;
    }
  }

  function defaultHearingType(group) {
    var val = getHearingTypeValue(group);
    if (val && val != '') {
      logDefaultFields('hearing_type', fields.hearing_type, val);
      fields.hearing_type = val;
    }
  }

  function defaultHearingSubType(group) {
    var val = getHearingSubTypeValue(group);
    if (val && val != '') {
      logDefaultFields('hearing_subtype', fields.hearing_subtype, val);
      fields.hearing_subtype = val;
    }
  }

  function defaultHearingMode(group) {
    var val = getHearingModeValue(group);
    if (val && val != '') {
      logDefaultFields('hearing_mode', fields.hearing_mode, val);
      fields.hearing_mode = val;
    }
  }

  function defaultPartyType(group) {
    var val = getPartyTypeValue(group);
    if (val && val != '') {
      logDefaultFields('party_type', fields.party_type, val);
      fields.party_type = val;
    }
  }

  function defaultOutcome(group) {
    var val = getOutcomeValue(group);
    if (val && val != '') {
      logDefaultFields('outcome', fields.outcome, val);
      fields.outcome = val;
    }
  }

  function defaultCourt(group) {
    var val = $(group.court).val();
    if (val && val != '') {
      logDefaultFields('court', fields.court, val);
      fields.court = val;
    }
  }

  function defaultPrArea(group) {
    var val = $(group.practice_area).val();
    if (val && val != '') {
      logDefaultFields('practice_area', fields.practice_area, val);
      fields.practice_area = val;
    }
  }

  function defaultJudge(group) {
    var val = filterData(group.judge);
    if (val && val != '') {
      logDefaultFields('judge', fields.judge, val);
      fields.judge = val;
    }
  }

  function defaultSelf(group) {
    var self = $(group.self_checked);
    if (self.attr('data-default') == 1) {
      var val = self.val();
      logDefaultFields('self', fields.self, val);
      fields.self = val;
    }
  }

  function defaultAppeals(group) {
    var appeals = $(group.appeals_checked);
    if (appeals.attr('data-default') == 1) {
      var val = appeals.val();
      logDefaultFields('appeals', fields.appeals, val);
      fields.appeals = val;
    }
  }

  function defaultIndCompany(group) {
    var indCompany = $(group.individual_company);
    if (indCompany.attr('data-default') == 1) {
      var val = indCompany.val();
      logDefaultFields('individual_company', fields.individual_company, val);
      fields.individual_company = val;
    }
  }

  function defaultDateRange(group) {
    var dateRange = $(group.datePicker);
    if (dateRange.attr('data-default') == 1) {
      var dates = useDefaultDatePicker(dateRange);

      fields.start_date = dates.start;
      fields.end_date = dates.end;
      fields.min_date = dates.min;
      fields.max_date = dates.max;
    }
  }

  // ======== Global events ========

  // Show right sidebar
  $(gl.refine).click(function () {
    $(this).hide()
    $(gl.close).show();
    $(gl.overlay).show();

    var sidebar = $('.right_sidebar');
    var wrapper = sidebar.find('.overflow-wrapper');

    // Set height of the right sidebar
    var height = document.documentElement.scrollHeight;

    if (height < 700) {
      height = 700;
    }

    sidebar.animate({
      height: height + 'px'
    }, 100);

    wrapper.css({height: height - 225});
    sidebar.fadeIn(1);
  });

  // Hide right sidebar
  $('#close, .toggle-sidebar').click(function () {
    $(gl.close).hide();
    $(gl.refine).show();

    var sidebar = $('.right_sidebar:visible');
    if (!sidebar) {
      return false;
    }

    sidebar.animate({
      height: 100 + '%'
    }, 100);

    sidebar.hide();
    $(gl.overlay).hide();
  });

  // Open decision in new window
  body.on('click', '.open-decision', (function () {
    var decision = $(this);
    var table = decision.closest('.result-table');
    var document = decision.attr('data-document');

    if (!table.hasClass('disable') && document) {
      decision.closest('tr').addClass('visited');

      // window.open('https://s3.amazonaws.com/' + document, '_blank');
      window.open('/decision/' + document, '_blank');
    }
  }));

  body.on('click', '.no-dec-access', function () {
    var message = 'You do not have access to the decision profile page.';
    message += ' Please sign up for a plan that provides access to this feature.';

    bootbox.dialog({
      message: message,
      buttons: {
        cancel: {
          label: 'OK',
          className: 'alt-button bootbox-close-button',
          callback: function () {
            return false;
          }
        }
      }
    });
  });

  body.delegate('.judge-decisions', 'click', function () {
    var id = +$(this).closest('td').attr('data-id');
    var court = +$(ha.court).val();
    var prArea = +$(ha.practice_area).val();
    var hearings = $(ha.hearings).val(); // can be a string

    var link = '/report?id=1';
    link += '&c=' + court;
    link += '&p=' + prArea;
    link += '&h=' + hearings;
    link += '&j=' + id;
    link += '&d=full';

    window.open(link, '_blank');
  });

  body.delegate('#run-case-law', 'click', function (e) {
    e.preventDefault();

    var court = +$(ha.court).val();
    var prArea = +$(ha.practice_area).val();
    var hearings = $(ha.hearings).val(); // can be a string

    var link = '/report?id=1';
    link += '&c=' + court;
    link += '&p=' + prArea;
    link += '&h=' + hearings;
    link += '&d=full';

    window.open(link, '_blank');
  });

  body.delegate('#run-outcome', 'click', function (e) {
    e.preventDefault();

    runSingleOutcome();
  });

  body.delegate('#run-full-outcome', 'click', function (e) {
    e.preventDefault();

    runSingleOutcome(true);
  });

  function runSingleOutcome(full) {
    var court = +$(ha.court).val();
    var prArea = +$(ha.practice_area).val();
    var hearings = $(ha.hearings).val(); // can be a string

    var link = '/report?id=2';
    link += '&c=' + court;
    link += '&p=' + prArea;
    link += '&h=' + hearings;

    if (full == undefined) {
      link += '&d=last';
    } else {
      link += '&d=full';
    }

    window.open(link, '_blank');
  }

  // Button link to download graph image
  $('#getCanvasImage').click(function (e) {
    e.preventDefault();

    var canvas = $('.canvasjs-chart-canvas')[0];

    downloadCanvas(this, canvas);
  });

  // Downloads graph image from canvas function
  function downloadCanvas(link, canvas) {
    link.href = canvas.toDataURL();

    link.download = 'graph.jpg';
  }

  // Show/hide next div
  body.delegate('.show-hide', 'click', function () {
    var inputGroup = $(this);
    var isActive = inputGroup.hasClass('active');

    var active = $('.show-hide.active');
    if (active.length > 0) {
      active.removeClass('active');
      active.next('div').slideUp();
    }

    if (!isActive) {
      inputGroup.addClass('active');
      inputGroup.next('div').slideDown();
    }
  });

  // Switcher
  body.delegate('.switcher input', 'change', function (e) {
    var radio = $(this);
    if (radio.is('[readonly]')) {
      return false;
    }

    threeStatesSwitch(radio.parent());
  });

  // "Use full available date range"
  body.delegate('.report-switch-date', 'click', function () {
    var selector = $(this).attr('data-datepicker');
    var datePicker = $(selector);

    var dates = getDatePickerDates(datePicker, getDatePickerFormat(true));

    datePicker.find('input[name="from"]').val(dates.min);
    datePicker.find('input[name="to"]').val(dates.max).trigger('change'); // Run events
  });

  // Tooltip
  body.delegate('.field-tooltip', 'mouseenter', function () {
    var elem = $(this);
    var parent = elem.parent();
    var tooltip = elem.attr('data-tooltip');

    if (tooltip && tooltip.length > 0) {
      var div = document.createElement('div');
      div.className = 'info1';
      div.innerHTML = tooltip;
      elem.after(div);

      var width = elem.outerWidth();
      var parentWidth = parent.outerWidth();
      var divWidth = $(div).outerWidth();
      var divHeight = -$(div).outerHeight();

      div.style = 'display: block;';

      if (parentWidth > divWidth) {
        $(div).css({top: divHeight / 2, left: width + 25});
      } else {
        $(div).css({top: divHeight - 5, left: (-(divWidth - width) / 2)});
      }

      if (elem.hasClass('switcher')) {
        $(div).css({top: '-20px'});
      }
    }
  })

  body.delegate('.field-tooltip', 'mouseleave', function () {
    body.find('.info1').fadeOut(100).remove();
  });

});

// ====== Report functions ======

var allowUpdateFields = 1;

function setFieldEvents(type) {
  // Update judge dropdown
  var sidebar = $(gl.rightPanelContent);
  var updateJudgeFacets = deferredUpdate(function (e) {
    var judge = $('#' + type + '-judge');
    var court = sidebar.find('#' + type + '-court').val();

    if ($(e.currentTarget).attr('data-role') != 'judge') {
      var single = (type == 'dt');

      getJudges(judge, court, judge.val(), single, true);
    }
  }, 3000);

  sidebar.delegate('input[type="text"]:not(#searchBoxDm), input[type="radio"], select', 'change', function (e) {
    if (allowUpdateFields == 1) {
      updateJudgeFacets(e);
    }
  });
}

function renderReport(result) {
  // Render result
  $(gl.resultWrapper).html(result);

  // Render pagination
  initialPagination();

  // Show "Refine" button
  $(gl.refine).show();
  $(gl.close).hide();

  // Hide Right panel
  $(gl.rightPanel).hide();

  // Show result
  $(gl.reportContainer).show();

  // Hide Report boxes
  $(gl.reportList).hide();

  // Hide grey background
  $(gl.overlay).hide();

  // Hide pre-loader
  $(gl.preLoader).fadeOut(100);
}

function decTatResponseHandler(result, firstRun) {
  if (result.error != undefined) {
    document.location.href = '/report';
  }

  // 'Decision Turnaround Time'
  changeTitle(fields.report_name, dtTitle);

  renderReport(result);

  if (firstRun) {
    $(gl.sentence).html('');

    renderDecTatSelects();

    getHearingDateRange(dt.hearRange, fields);

    testReportFields('complete');

    setFieldEvents('dt');
  }
}

// Reset Case Law
function resetCaseLaw() {
  resetSwitcher('cl-appeals', cl.defaultValue.appeals);
  resetSwitcher('cl-self', cl.defaultValue.self);

  // Proceeding Type
  $(cl.proceeding_type).val(cl.defaultValue.proc_cat);

  // Case Tags
  $(cl.case_tags).val(cl.defaultValue.case_tags);

  // Claim Type
  $(cl.claim_type).val(cl.defaultValue.claim_type);

  // Hearing Mode
  $(cl.hearing_mode).val(cl.defaultValue.hearing_mode);

  // Judge
  $(cl.judge).val(cl.defaultValue.judge);

  // Outcome
  $(cl.outcome).val(cl.defaultValue.outcome);

  // Case Category
  $(cl.case_category).val(cl.defaultValue.case_category);

  // Hearings
  $(cl.hearings).val(cl.defaultValue.hearings);

  // Individual/Company
  $(cl.individual_company).val(cl.defaultValue.individual_company);

  // Party Type
  $(cl.party_type).val(cl.defaultValue.party_type);
}

// Reset Outcome Report
function resetOutcome() {
  resetSwitcher('or-appeals', or.defaultValue.appeals);
  resetSwitcher('or-self', or.defaultValue.self);

  // --------------------------

  // Only show me results where all parties on the same side of the proceedings share the same outcome
  $('#hear_decision').prop("checked", false).val(or.defaultValue.hearing_decision);

  // Proceeding Type
  $(or.proceeding_type).val(or.defaultValue.proceeding_type);

  // Case Category
  $(or.case_category).val(or.defaultValue.case_category);

  // Claim Type
  $(or.claim_type).val(or.defaultValue.claim_type);

  // Hearing Mode
  $(or.hearing_mode).val(or.defaultValue.hearing_mode);

  // Hearings
  $(or.hearings).val(or.defaultValue.hearings);

  // Case Tags
  $(or.case_tags).val(or.defaultValue.case_tags);

  // Company/Individual
  $(or.individual_company).val(or.defaultValue.individual_company);

  // Party Type
  $(or.party_type).val(or.defaultValue.party_type);

  // Moving Party
  $(or.moving_party).val(or.defaultValue.moving_party);

  // Judge
  $(or.judge).val(or.defaultValue.judge);
}

// Reset Dec Tat
function resetDecTat() {
  resetSwitcher('dt-appeals', dt.defaultValue.appeals);
  resetSwitcher('dt-self', dt.defaultValue.self);

  // Proceeding Type
  $(dt.proceeding_type).val(dt.defaultValue.proceeding_type);

  // Case Category
  $(dt.case_category).val(dt.defaultValue.case_category);

  // Case Tags
  $(dt.case_tags).val(dt.defaultValue.case_tags);

  // Claim Type
  $(dt.claim_type).val(dt.defaultValue.claim_type);

  // Hearings
  $(dt.hearings).val(dt.defaultValue.hearings);

  // Hearing Mode
  $(dt.hearing_mode).val(dt.defaultValue.hearing_mode);

  // Party Type
  $(dt.party_type).val(dt.defaultValue.party_type);

  // Individual/Company
  $(dt.individual_company).val(dt.defaultValue.individual_company);

  // Outcome
  $(dt.outcome).val(dt.defaultValue.outcome);

  // Judge
  var courtId = $(gl.court).val() || $('#court').text() || $('input[name="court"]').val();
  var judgeId = $('#judgeId').text() || $('input[name="judge"]').val();
  getJudges(dt.judge, courtId, judgeId, true, true);
}

// Reset Personal Injury Costs Report
function resetInjuryCosts() {
  resetSwitcher('ic-appeals', ic.defaultValue.appeals);
  resetSwitcher('ic-self', ic.defaultValue.self);

  // Proceeding Type
  $(ic.proceeding_type).val(ic.defaultValue.proc_cat);

  // Case Tags
  $(ic.case_tags).val(ic.defaultValue.case_tags);

  // Claim Type
  $(ic.claim_type).val(ic.defaultValue.claim_type);

  // Hearing Mode
  $(ic.hearing_mode).val(ic.defaultValue.hearing_mode);

  // Judge
  $(ic.judge).val(ic.defaultValue.judge);

  // Outcome
  $(ic.outcome).val(ic.defaultValue.outcome);

  // Case Category
  $(ic.case_category).val(ic.defaultValue.case_category);

  // Hearings
  $(ic.hearings).val(ic.defaultValue.hearings);

  // Individual/Company
  $(ic.individual_company).val(ic.defaultValue.individual_company);

  // Party Type
  $(ic.party_type).val(ic.defaultValue.party_type);
}

// -----------------

// Render Case Law selects
function renderCaseLawSelects() {
  // Proceeding Type
  addSerfSelect(cl.proceeding_type, noNested);

  // Case Tags
  addSerfSelect(cl.case_tags, noNested);

  // Claim Type
  addSerfSelect(cl.claim_type, noNested);

  // Hearing Mode
  addSerfSelect(cl.hearing_mode, noNested);

  // Judge
  addSerfSelect(cl.judge, noNested);

  // Outcome
  addSerfSelect(cl.outcome, noNested);

  // Case Category
  addSerfSelect(cl.case_category, collapsed);

  // Hearings
  addSerfSelect(cl.hearings, {});

  // Individual/Company
  addSerfSelect(cl.individual_company, single);

  // Party Type
  addSerfSelect(cl.party_type, noNested);
}

// Render Outcome Report selects
function renderOutcomeSelects() {
  // Proceeding Type
  addSerfSelect(or.proceeding_type, noNested);

  // Case Category
  addSerfSelect(or.case_category, {});

  // Claim Type
  addSerfSelect(or.claim_type, noNested);

  // Hearing Mode
  addSerfSelect(or.hearing_mode, noNested);

  // Hearings
  addSerfSelect(or.hearings, {});

  // Case Tags
  addSerfSelect(or.case_tags, noNested);

  // Company/Individual
  addSerfSelect(or.individual_company, single);

  // Party Type
  addSerfSelect(or.party_type, single);

  // Moving Party
  addSerfSelect(or.moving_party, single);

  console.log($(or.party_type));
  console.log($(or.moving_party));

  // Hide "Moving or Responding Party" option
  $(or.moving_party).next('.serfSelect').find('.selections .item[data-value="2"]').css('display', 'none');

  // Judge
  addSerfSelect(or.judge, noNested);
}

// Render Dec Tat selects
function renderDecTatSelects() {
  // Proceeding Type
  addSerfSelect(dt.proceeding_type, noNested);

  // Case Category
  addSerfSelect(dt.case_category, {});

  // Case Tags
  addSerfSelect(dt.case_tags, noNested);

  // Claim Type
  addSerfSelect(dt.claim_type, noNested);

  // Hearings
  addSerfSelect(dt.hearings, {});

  // Hearing Mode
  addSerfSelect(dt.hearing_mode, noNested);

  // Party Type
  addSerfSelect(dt.party_type, noNested);

  // Individual/Company
  addSerfSelect(dt.individual_company, single);

  // Outcome
  addSerfSelect(dt.outcome, noNested);

  // Judge
  addSerfSelect(dt.judge, single);
}

// Render Personal Injury Costs Report selects
function renderInjuryCostsSelects() {
  // Proceeding Type
  addSerfSelect(ic.proceeding_type, noNested);

  // Case Tags
  addSerfSelect(ic.case_tags, noNested);

  // Claim Type
  addSerfSelect(ic.claim_type, noNested);

  // Hearing Mode
  addSerfSelect(ic.hearing_mode, noNested);

  // Judge
  addSerfSelect(ic.judge, noNested);

  // Outcome
  addSerfSelect(ic.outcome, noNested);

  // Case Category
  addSerfSelect(ic.case_category, collapsed);

  // Hearings
  addSerfSelect(ic.hearings, {});

  // Individual/Company
  addSerfSelect(ic.individual_company, single);

  // Party Type
  addSerfSelect(ic.party_type, noNested);
}

// -----------------

function setReportFields(prefix, firstRun, defaultSettings, ignoreNames) {
  var group = this[prefix];

  if (group == undefined) {
    return false;
  }

  if (firstRun && !defaultSettings) {
    var datePicker = $(gl.datePicker);
  } else {
    datePicker = $(group.datePicker);
  }

  var dates = getDatePickerDates(datePicker);
  var hearDates = getDatePickerDates(group.hearRange);

  fields.proceeding_category = getProceedingCategoryValue(group);
  fields.proceeding_type = getProceedingTypeValue(group);
  fields.proceeding_subtype = getProceedingSubTypeValue(group);
  fields.case_tags = getCaseTagsValue(group);
  fields.claim_type = getClaimTypeValue(group);
  fields.hearing_type = getHearingTypeValue(group);
  fields.hearing_subtype = getHearingSubTypeValue(group);
  fields.hearing_mode = getHearingModeValue(group);
  fields.party_type = getPartyTypeValue(group);
  fields.individual_company = getIndividualCompanyValue(group);
  fields.self = getSelfValue(group);
  fields.appeals = getAppealsValue(group);
  fields.court = (defaultSettings) ? $(gl.defCourt).val() : $(group.court).val();
  fields.practice_area = (defaultSettings) ? $(gl.defPrArea).val() : $(group.practice_area).val();
  fields.start_date = dates.start;
  fields.end_date = dates.end;
  fields.min_date = dates.min;
  fields.max_date = dates.max;
  fields.start_hear = hearDates.start;
  fields.end_hear = hearDates.end;

  if (prefix == 'or') {
    var moving = $(or.moving_party).val();
    fields.moving_party = moving ? moving : 1;
    fields.hearing_decision = ($('input[name="share_same_outcome"]:checked').val()) ? 1 : 0;
  } else {
    fields.outcome = getOutcomeValue(group);
  }

  if (prefix == 'dt') {
    fields.judge = $(dt.judge).val();
  } else {
    fields.judge = filterData(group.judge);
  }

  fields = checkIncorrectValues(fields);

  if (ignoreNames == undefined) {
    fields.names = getFieldValueNames();
  } else {
    fields.names = [];
  }
}

// Get the names of field values
function getFieldValueNames(section) {
  var names = {};
  var tagsLimit = 5;

  if (section == undefined) {
    section = $(gl.rightPanelContent);
  }

  // Dropdowns
  var f = section.find('select');

  f.each(function (k, v) {
    var field = $(v);
    var value = field.val();
    var name = field.attr('data-role');

    if (name != undefined) {
      if (Array.isArray(value)) { // Several options are selected
        var opts = field.find('option').filter(function () {
          return $(this).val() > 0;
        }); // skip zero value
        var selected = field.find('option:selected');

        /*
         if selected all
         show "All"
         else
         if selected more than 10 options
         if options count minus selected options count less or equal 5
         show excluded options in red
         else
         show 5 first options + "[remaining count] more"
         else
         show these selected options
         */

        if (opts.length == selected.length) { // All options are selected
          names[name] = {
            value: 'All',
            count: opts.length,
          };

        } else {
          value = [];

          // For dropdowns with more than 10 elements
          if (opts.length > 10) {
            var notSelected = field.find('option:not(:selected)').filter(function () {
              return $(this).val() > 0;
            });

            if ((opts.length - selected.length) <= tagsLimit) { // Exclusion
              notSelected.each(function (k, v) {
                var attrName = $(v).attr('data-name');
                value.push(attrName ? attrName : $(v).text().trim());
              });

              names[name] = {
                value: value,
                not: true,
                count: notSelected.length,
              };

            } else {
              selected.each(function (k, v) {
                var attrName = $(v).attr('data-name');
                value.push(attrName ? attrName : $(v).text().trim());
              });

              var first = value.slice(0, tagsLimit);

              names[name] = {
                value: first,
                count: selected.length,
              };

              var diff = value.length - tagsLimit;
              if (diff > 0) {
                names[name].more = diff;
              }
            }

          } else {
            selected.each(function (k, v) {
              var attrName = $(v).attr('data-name');
              value.push(attrName ? attrName : $(v).text().trim());
            });

            names[name] = {
              value: value,
              count: selected.length,
            };
          }
        }

      } else {
        var text = field.find('option:selected').text().trim();
        if (name == 'company-individual' && value == 2) {
          text = '';
        }

        if (text != '') {
          names[name] = {
            value: text,
            count: 1
          };
        }
      }
    }
  });

  // Switchers
  f = section.find('input[type="radio"]:checked');
  f.each(function (k, v) {
    var field = $(v);
    var value = field.val();
    var name = field.attr('data-role');
    var text = field.attr('data-title');

    if (name && value != 2 && text != '') {
      names[name] = {
        value: text,
        count: 1
      };
    }
  });

  return names;
}

function updateHearingDate(prefix) {
  getHearingDateRange('#' + prefix + '-hear-date-range', fields);
}

// Checks whether default settings are available
function useDefaultReportSettings() {
  // Court
  var defaultCourt = $(gl.defCourt);
  var defaultCourtVal = (defaultCourt.length == 1) ? defaultCourt.val() : 0;
  if (!defaultCourtVal > 0) {
    showDefaultSettingsAlert();
    return false;
  }

  // Practice Area
  var defaultPrArea = $(gl.defPrArea);
  var defaultPrAreaVal = (defaultPrArea.length == 1) ? defaultPrArea.val() : 0;
  if (!defaultPrAreaVal > 0) {
    showDefaultSettingsAlert();
    return false;
  }

  return true;
}

function getGraphData(selector) {
  var val = (getHiddenData(selector).hearCountPerc.toFixed() !== 'NaN')
      ? getHiddenData(selector).hearCountPerc.toFixed(2)
      : 0;

  return parseFloat(val);
}

function makeGraph(selector, data) {
  var graph = new CanvasJS.Chart(selector, data);
  graph.render();
}

function getOutcomeGraphOptions() {
  var other = getGraphData('.Order');
  other += getGraphData('.Addressed_Obiter_Dicta');
  other += getGraphData('.Not_addressed');
  other += getGraphData('.No_Outcome');
  other = parseFloat((Math.round(other * 100) / 100).toFixed(2));

  CanvasJS.addColorSet("newShades",
      [// colorSet Array
        "#8aec00", // win bar
        "#fa1038", // loss bar
        "#50c2c3", // split bar
        "#a349a4", // other bar
        // "#fb9b00", // order bar
        // "#00CD38", // addressed bar
        // "#05A8AA", // not addressed
        // "#2364AA", // no outcome
        // "#5E239D", // other
      ]
  );

  return {
    colorSet: "newShades",
    animationEnabled: true,
    animationDuration: 2000,
    axisX: {
      /*
       title: "Outcome",
       titleFontSize: 18,
       titleFontFamily: 'HelveticaNeueLTStd-Roman',
       titleFontWeight: "bold",
       titleFontColor: "#000", // 12114C
       */
      labelFontSize: 14,
      labelFontColor: "black",
      labelFontWeight: "normal",
      labelMaxWidth: 120,
      tickThickness: 1,
      gridThickness: 0
    },
    axisY: {
      title: "Percentage of Hearings (%)",
      titleFontSize: 18,
      titleFontColor: "#000", // 12114C
      titleFontWeight: "bold",
      titleFontFamily: 'HelveticaNeueLTStd-Roman',
      labelFontSize: 14,
      labelFontColor: "black",
      labelFontWeight: "normal",
      maximum: 100,
      tickThickness: 1,
      gridThickness: 0
    },
    theme: "theme1",
      toolTip: {
          content: "{indexLabel}",
      },
    data: [
      {
        type: "bar",
          indexLabelFontSize: 12,
          yValueFormatString:"0.00",
          indexLabelFontFamily:"HelveticaNeueLTStd-Roman",
          indexLabelFontColor: "#7b7d7f",
          dataPoints: [
          {y: getGraphData('.Win'), indexLabel: " Win: "+getGraphData('.Win')+"%", label: "Win"},
          {y: getGraphData('.Loss'), indexLabel: " Loss: "+getGraphData('.Loss')+"%", label: "Loss"},
          {y: getGraphData('.Split'), indexLabel: " Split: "+getGraphData('.Split')+"%", label: "Split"},
          {y: other, indexLabel: " Other: "+other+"%", label: "Other"}

          // {y: getGraphData('.Order'), label: "Order"},
          // {y: getGraphData('.Addressed_Obiter_Dicta'), label: "Addressed Obiter Dicta"},
          // {y: getGraphData('.Not_addressed'), label: "Not Addressed"},
          // {y: getGraphData('.No_Outcome'), label: "No Outcome"},
          // { y: parseFloat(outcomeData.Other), label: "Other %"  }
        ]
      }
    ]
  };
}

function getDecTatGraphOptions(ranges) {
  CanvasJS.addColorSet('newShades',
      [// colorSet Array
        '#2364AA', // 0-25
        '#05A8AA', // 25-50
        '#82EB38', // 50-75
        '#FF9900', // 75-100
        '#FF003C', // 100+
      ]
  );

  return {
    colorSet: 'newShades',
    animationEnabled: true,
    animationDuration: 2000,
    axisX: {
      title: 'Average Turnaround (in Days)',
      titleFontSize: 14,
      titleFontFamily: 'HelveticaNeueLTStd-Roman',
      titleFontWeight: 'bold',
      titleFontColor: '#000',
      labelFontSize: 11,
      labelFontColor: 'black',
      labelFontWeight: 'normal',
      labelMaxWidth: 120,
      tickThickness: 1,
      gridThickness: 0
    },
    axisY: {
      title: 'Number of Judges/Masters',
      titleFontSize: 14,
      titleFontColor: '#000',
      titleFontWeight: 'bold',
      titleFontFamily: 'HelveticaNeueLTStd-Roman',
      labelFontSize: 11,
      labelFontColor: 'black',
      labelFontWeight: 'normal',
      tickThickness: 1,
      gridThickness: 1
    },
    theme: 'theme1',
    dataPointWidth: 40, // column width
    data: [
      {
        type: 'column',
        yValueFormatString: '0 judges',
        dataPoints: [
          {y: parseInt(ranges.cnt0_25), label: '25.00 or fewer'},
          {y: parseInt(ranges.cnt25_50), label: '25.01 to 50.00'},
          {y: parseInt(ranges.cnt50_75), label: '50.01 to 75.00'},
          {y: parseInt(ranges.cnt75_100), label: '75.00 to 100.00'},
          {y: parseInt(ranges.cnt100), label: '100.01 or greater'},
        ]
      }
    ]
  };
}

function checkIncorrectValues(object) {
  $.each(object, function (key, value) {
    if (value == undefined) {
      object[key] = '';
    }
  });

  return object;
}

function checkMandatoryFields() {
  var wrappers = $('.right-panel-content').find('.required');
  var inputs = wrappers.find('select').not(':disabled');
  var valid = true;
  var fieldNames = [];

  wrappers.removeClass('has-error');

  $.each(inputs, function (k, v) {
    var input = $(v);

    if (!input.val()) {
      var wrapper = input.closest('.required');
      wrapper.addClass('has-error');
      valid = false;

      fieldNames.push(wrapper.find('.field-label').text());
    }
  });

  if (fieldNames.length) {
    var message = 'Please select a ';
    message += fieldNames.join(', ');
    message += ' to continue.';

    bootbox.dialog({
      message: message,
      buttons: {
        cancel: {
          label: 'OK',
          className: 'alt-button bootbox-close-button',
          callback: function () {
            return false;
          }
        }
      }
    });

    return false;
  }

  return valid;
}

function getHiddenData(selector) {
  var decCountTotal = $('input[name="decisionsCountTotal"]').val();

  return {
    hearCount: $(selector + '_data input[name="hearingsCount"]').val(),
    decCount: $(selector + '_data input[name="decisionsCount"]').val(),
    hearCountTotal: $('input[name="hearingsCountTotal"]').val(),
    decCountTotal: decCountTotal,
    hearCountPerc: parseFloat($(selector + '_percent').val()),
    decCountPerc: (parseInt($(selector + '_data input[name="decisionsCount"]').val()) * 100) / decCountTotal
  };
}

// Set values from "General" section of report settings
function setGeneralReportSettings(prefix) {
  var dates = getDatePickerDates('#' + prefix + '-date-range');

  fields.court = $('#' + prefix + '-court').val();
  fields.practice_area = $('#' + prefix + '-practice-area').val();
  fields.start_date = dates.start;
  fields.end_date = dates.end;
  fields.min_date = dates.min;
  fields.max_date = dates.max;
}

// change title in header when you open new report
function changeTitle(title, subtitle) {
  $('.report_center_txt').text(title);
  $('.sub-bar .col-xs-12').text(subtitle);
  $(gl.refine).css('display', 'block');
}

// Get the top-level values of multiple select
function selectedTypes(selector) {
  var type = [];
  var test = 0;

  $(selector + " option:selected").each(function () {
    if (test != $(this).attr("data-index")) {
      if ($(this).val() != '' && $(this).attr("data-index") != '') {
        type.push($(this).attr("data-index"));
      }
      test = $(this).attr("data-index");
    }
  });

  return type;
}

// Get the sub-level values of multiple select
function selectedValues(selector) {
  var values = [];

  $(selector + " option:selected").each(function () {
    if ($(this).val() != '') {
      values.push($(this).val());
    }
  });

  return values;
}

function joinData(data) {
  return data.join();
}

function filterDataFromSelect(value) {
  var array = $(value).toArray();
  return array.join();
}

function filterData(selector) {
  return filterDataFromSelect($(selector).val())
}

function addSerfSelect(selector, serfOptions) {
  var field = $(selector);
  var options = field.find('option');

  // If the field is mandatory and has only one option - make it selected
  if (options.length <= 2 && field.attr('data-required')) {
    var singleOption = null;
    $.each(options, function (k, v) {
      // Skip "All" option
      var val = $(v).val();
      if (val > 0) {
        singleOption = (singleOption == null) ? val : false;
      }
    });

    if (singleOption) {
      field.val(singleOption);
    }
  }

  if (!field.hasClass('not-for-render')) {
    field.serfSelect(serfOptions);
  }
}

// Update switcher after changing
function threeStatesSwitch(switcher) {
  var state1 = switcher.find('.switcher-radio-neutral:checked ~ .switcher-slider');
  var state2 = switcher.find('.switcher-radio-off:checked ~ .switcher-slider');
  var state3 = switcher.find('.switcher-radio-on:checked ~ .switcher-slider');

  var wrapper = $('.' + switcher.attr('id') + '-switcher');
  var switcher1 = wrapper.find('.switcher-pos-1');
  var switcher2 = wrapper.find('.switcher-pos-2');
  var switcher3 = wrapper.find('.switcher-pos-3');

  var description = '';

  if (state1.length == 1) { // Include
    description = switcher1.attr('data-description');

    switcher2.animate({
      color: '#666'
    }, 300);
    switcher2.css('font-weight', '400');

    switcher1.animate({
      color: '#333'
    }, 300);
    switcher1.css('font-weight', '700');

    switcher3.animate({
      color: '#666'
    }, 300);
    switcher3.css('font-weight', '400');
  }

  if (state2.length == 1) { // Exclude
    description = switcher2.attr('data-description');

    switcher2.animate({
      color: '#333'
    }, 300);
    switcher2.css('font-weight', '700');

    switcher1.animate({
      color: '#666'
    }, 300);
    switcher1.css('font-weight', '400');

    switcher3.animate({
      color: '#666'
    }, 300);
    switcher3.css('font-weight', '400');
  }

  if (state3.length == 1) { // Only
    description = switcher3.attr('data-description');

    switcher2.animate({
      color: '#666'
    }, 300);
    switcher2.css('font-weight', '400');

    switcher1.animate({
      color: '#666'
    }, 300);
    switcher1.css('font-weight', '400');

    switcher3.animate({
      color: '#333'
    }, 300);
    switcher3.css('font-weight', '700');
  }

  // Change switch description
  wrapper.parent().find('.switch-description').text(description);
}

// Set default value to switcher
function resetSwitcher(switcherName, defaultValue) {
  var switcher = $('#' + switcherName);
  switcher.find('input[value="' + defaultValue + '"]').prop('checked', true).trigger('change');
}

// ====== Get functions ======

function getHearings(selector, value, court, isRender) {
  if (court == 'undefined' || court == '') {
    court = 2;
  }

  var data = {
    court: court,
    master: fields.master,
    report_id: fields.report_id
  };

  $.get('/report/get-hearings', data, function (resp) {
    var select = $(selector);

    select.html(resp);
    select.prop('disabled', false);

    if (value != undefined) {
      select.val(value);
    }

    if (isRender != undefined) {
      addSerfSelect(select, collapsed);
    }
  });
}

function getProceedingType(selector, court, isRender) {
  var data = {
    court: court,
    master: fields.master,
    report_id: fields.report_id
  };

  $.get('/report/get-proceeding-type', data, function (resp) {
    if (resp) {
      var select = $(selector);

      select.html(resp);
      select.val([]);

      if (isRender != undefined) {
        addSerfSelect(selector, noNested);
      }
    }
  });
}

function getPartyType(selector, court, isRender) {
  var data = {
    court: court,
    master: fields.master,
    report_id: fields.report_id
  };

  $.get('/report/get-party-type', data, function (resp) {
    if (resp) {
      var select = $(selector);
      select.html(resp);

      var opts = noNested;

      if (selector == or.party_type) {
        checkMovingParty(select);
        opts = single;
      } else {
        select.find('option[value="0"]').remove();
        select.val([]);
      }

      if (isRender != undefined) {
        addSerfSelect(selector, opts);
      }
    }
  });
}

function checkMovingParty(partyType, keepMovingParty) {
  if (keepMovingParty == undefined) {
    $(or.moving_party).val(1).serfSelect(single); // "Moving Party" option
  }

  var movOrResp = $(or.moving_party).next('.serfSelect').find('.selections .item[data-value="2"]');

  if (partyType.val() == 0) {
    movOrResp.css('display', 'none');
  } else {
    movOrResp.css('display', 'block');
  }
}

function getJudges(selector, court, selectedValue, isSingle, isRender) {
  var select = $(selector);

  if (select.length == 0) {
    return false;
  }

  if (court == 'undefined' || court == '') {
    court = 2;
  }

  if (isSingle == undefined || isSingle == false) {
    var allOptions = 'no';
    var opts = noNested;
  } else {
    opts = single;
    allOptions = 'yes';
  }

  // Update values
  setReportFields(getPrefix(select), null, null, true);

  var data = $.extend({}, fields);
  data.data = [];
  data.court = court;
  data.allOption = allOptions;

  $.get('/report/get-judge', data, function (resp) {
    if (resp) {
      select.html(resp)
      select.prop('disabled', false);

      if (selectedValue != undefined) {
        select.val(selectedValue);
      } else {
        select.val([0]);
      }

      if (isRender != undefined) {
        addSerfSelect(selector, opts);
      }
    }
  });
}

function getCaseCategory(selector, prArea) {
  var data = {
    practice_area: prArea,
    master: fields.master,
    report_id: fields.report_id
  };

  $.get('/report/get-case-category', data, function (resp) {
    if (resp) {
      var select = $(selector);
      select.html(resp);

      addSerfSelect(selector, collapsed);
    }
  });
}

function getHearingDateRange(selector, fields) {
  $.post('/report/get-hearing-date-range', {
    court: fields.court,
    practice_area: fields.practice_area,
    start_date: fields.start_date,
    end_date: fields.end_date
  }, function (response) {
    if (response.error == undefined) {
      renderDate(selector, '', '', response.min, response.max);
    }
  });
}

function checkHearings(selector, settings) {
  var select = $(selector).next('.serfSelect').find('.item[data-index="' + settings.hearing_type + '"]:first');
  var subSection = select.closest('.section');
  var section = subSection.parent('.section');

  if (section.length) {
    section.children('.title').find('input[type="checkbox"]').trigger('click');
  } else {
    subSection.children('.title').find('input[type="checkbox"]').trigger('click');
  }
}

function getPrefix(select) {
  var id = select.attr('id');
  if (id.length) {
    return id.substring(0, 2);
  }

  return '';
}

// ====== Confirm functions ======

function confirmOutcomeWithoutPartyType(callback) {
  var message = '<div class="row mb-20">';
  message += '<div class="col-xs-3 pr-5 text-right">';
  message += '<i class="fa fa-arrow-right clr-dark-red mt-20 fs-30"></i></div>';
  message += '<div class="col-xs-6 warning-box">';
  message += '<label>Party Type <span class="numberCircle">+</span></label>';
  message += '<select class="form-control"></select></div></div>';

  message += '<div class="row"><div class="col-xs-10 col-xs-offset-1">';
  message += "You've chosen an Outcome but no Party Type.";
  message += " You can run this report, but it will return results where any party has that outcome.";
  message += " For instance, if you chose 'Win', it will return results where any party won.";
  message += " Do you still wish to proceed?";
  message += '</div></div>';

  bootbox.dialog({
    title: '<span class="clr-dark-red"><i class="fa fa-exclamation-circle"></i> Report Settings Alert</span>',
    message: message,
    buttons: {
      cancel: {
        label: 'No, take me back',
        className: 'btn-danger reset-report-cancel bootbox-close-button',
        callback: function () {
          return false;
        }
      },
      yes: {
        label: 'Yes',
        className: 'btn-primary bootbox-close-button',
        callback: callback
      }
    }
  });
}

function confirmReportReset(callback) {
  var message = '<h4 class="reset-report-header">';
  message += 'Are you sure you want to reset all report fields to their default settings?</h4>';
  message += '<p class="reset-report-message">You will lose any changes you have made.</p>';

  bootbox.dialog({
    message: message,
    buttons: {
      cancel: {
        label: 'Cancel',
        className: 'btn-default reset-report-cancel bootbox-close-button',
        callback: function () {
          return false;
        }
      },
      yes: {
        label: 'Yes',
        className: 'alt-button',
        callback: callback
      }
    }
  });
}

function showDefaultSettingsAlert() {
  var message = 'To run a quick report, please set your ';
  message += '<a href="/account-settings/report" target="_blank">default report settings</a>.';
  message += ' Note that you must set an option for all four required settings (Province, Court,';
  message += ' Practice Area, and Date) in order to use the quick reports feature.';

  bootbox.dialog({
    message: message,
    buttons: {
      cancel: {
        label: 'OK',
        className: 'alt-button bootbox-close-button',
        callback: function () {
          return false;
        }
      }
    }
  });
}

function deferredUpdate(func, ms) {
  var isThrottled = false,
      savedArgs,
      savedThis;

  function wrapper() {
    if (isThrottled) {
      savedArgs = arguments;
      savedThis = this;
      return;
    }

    func.apply(this, arguments);

    isThrottled = true;

    setTimeout(function () {
      isThrottled = false;
      if (savedArgs) {
        wrapper.apply(savedThis, savedArgs);
        savedArgs = savedThis = null;
      }
    }, ms);
  }

  return wrapper;
}

// ========= Get Input value =========

function getProceedingCategoryValue(group) {
  return filterData(group.proceeding_type) || group.defaultValue.proceeding_type;
}

function getProceedingTypeValue(group) {
  return joinData(selectedTypes(group.case_category)) || group.defaultValue.proceeding_type;
}

function getProceedingSubTypeValue(group) {
  return joinData(selectedValues(group.case_category)) || group.defaultValue.proceeding_type;
}

function getCaseTagsValue(group) {
  return filterData(group.case_tags) || group.defaultValue.case_tags;
}

function getClaimTypeValue(group) {
  return filterData(group.claim_type) || group.defaultValue.claim_type;
}

function getHearingTypeValue(group) {
  return joinData(selectedTypes(group.hearings)) || group.defaultValue.hearing_type;
}

function getHearingSubTypeValue(group) {
  return joinData(selectedValues(group.hearings)) || group.defaultValue.hearing_subtype;
}

function getHearingModeValue(group) {
  return filterData(group.hearing_mode) || group.defaultValue.hearing_mode;
}

function getPartyTypeValue(group) {
  return filterData(group.party_type) || group.defaultValue.party_type;
}

function getIndividualCompanyValue(group) {
  return $(group.individual_company).val() || group.defaultValue.individual_company;
}

function getOutcomeValue(group) {
  return filterData(group.outcome) || group.defaultValue.outcome;
}

function getAppealsValue(group) {
  return $(group.appeals_checked).val() || group.defaultValue.appeals;
}

function getSelfValue(group) {
  return $(group.self_checked).val() || group.defaultValue.self;
}

// ========= Testing =========

function logDefaultFields(fieldName, oldVal, newVal) {
}
function testReportFields(message) {
}
