
    /**
     * Show annotations
     * @param frameId
     * @param contentId
     * @param content
     * @param options_inp
     */
    function showAnnotations(frameId, contentId, contents, opt_types_inp, opt_classes_inp, opt_entities_inp) {
        
        var strres; 
        var frame;
        var opt_types;
        var opt_classes;
        var opt_entities;

        if ( frameId && typeof frameId != "undefined" && frameId.length ) {
            frame = $( "#"+frameId );
        } else {
            frame = $( "#main-frame" );
        }

        if ( contentId && typeof contentId != "undefined" && contentId.length ) {
            strres = "#" + contentId;
        } else {
            strres = "#result";
        }

        if ( contents && typeof contents != "undefined" &&  contents.length ) {
            frame.html( contents );
        }

        if (opt_entities_inp && typeof opt_entities_inp != "undefined" && opt_entities_inp.length) {
            opt_entities = opt_entities_inp;
        }
        else {
            opt_entities = [
                {text: 'FieldTerminology', property: 'text'},
                {text: 'Organization', property: 'text'},
                {text: 'Person', property: 'text'},
                {text: 'StateOrCounty', property: 'text'},
                {text: 'JobTitle', property: 'text'},
                {text: 'City', property: 'text'},
                {text: 'Company', property: 'text'},
                {text: 'Facility', property: 'text'},
                {text: 'Automobile', property: 'text'},
                {text: 'Sport', property: 'text'}
            ];
        }

        if (opt_types_inp && typeof opt_types_inp != "undefined" && opt_types_inp.length) {
            opt_types = opt_types_inp;
        }

        if (opt_classes_inp && typeof opt_classes_inp != "undefined" && opt_classes_inp.length) {
            opt_classes = opt_classes_inp;
        }
        else {
            var header = '<h5 style="padding: 2px 5px;" > Class entities </h5>';
            opt_classes =
            {
                element: '.annotator-hl',
                options: [{header: header},
                    {
                        text: 'Decision Overview',
                        subMenu: [
                            {text: 'DO Item 1', href: '#'},
                            {text: 'DO Item 2', href: '#'},
                            {text: 'DO Item 3', href: '#'},
                            {text: 'DO Item 4', href: '#'}
                        ],
                        href: '#'
                    },
                    {
                        text: 'Proceeding Overview',
                        subMenu: [
                            {text: 'PO Item 1', href: '#'},
                            {text: 'PO Item 2', href: '#'},
                            {text: 'PO Item 3', href: '#'},
                            {text: 'PO Item 4', href: '#'}
                        ],
                        href: '#'
                    },
                    //{   divider: true},
                    {
                        text: 'Partes in Proceeding',
                        subMenu: [{
                            text: 'Party Name',
                            subMenu: [{text: 'As Is', href: '#'},
                                {
                                    text: 'Alias To',
                                    subMenu: [{text: 'AT Item 1', href: '#'},
                                        {text: 'AT Item 2', href: '#'},
                                        {text: 'AT Item 3', href: '#'},
                                        {text: 'AT Item 4', href: '#'}],
                                    href: '#'
                                }],
                            href: '#'
                        },
                            {text: 'Party Type', href: '#'},
                            {text: 'Party Position', href: '#'},
                            {text: 'Is Company', href: '#'}
                        ],
                        href: '#'
                    },
                    {
                        text: 'Counsel',
                        subMenu: [
                            {text: 'Counsel Item 1', href: '#'},
                            {text: 'Counsel Item 2', href: '#'},
                            {text: 'Counsel Item 3', href: '#'},
                            {text: 'Counsel Item 4', href: '#'}],
                        href: '#'
                    },
                    {
                        text: 'Hearing Overview',
                        subMenu: [
                            {text: 'HO Item 1', href: '#'},
                            {text: 'HO Item 2', href: '#'},
                            {text: 'HO Item 3', href: '#'},
                            {text: 'HO Item 4', href: '#'}
                        ],
                        href: '#'
                    },
                    {
                        text: 'Partes in Hearing',
                        subMenu: [
                            {text: 'PiH Item 1', href: '#'},
                            {text: 'PiH Item 2', href: '#'},
                            {text: 'PiH Item 3', href: '#'},
                            {text: 'PiH Item 4', href: '#'}
                        ],
                        href: '#'
                    },
                    //{   divider: true},
                    {
                        text: 'Hearing Outcome',
                        subMenu: [
                            {text: 'HO Item 1', href: '#'},
                            {text: 'HO Item 2', href: '#'},
                            {text: 'HO Item 3', href: '#'},
                            {text: 'HO Item 4', href: '#'}
                        ],
                        href: '#'
                    }]
            }
        }

        $( strres )
            .annotator()
            .annotator('addPlugin', 'Store',
                { // The endpoint of the store on your server.
                    prefix: '/',

                    // Attach the uri of the current page to all annotations to allow search.
                    annotationData: {
                        'uri': 'learning/index/annotations'
                    },

                    /* This will perform a "search" action when the plugin loads.
                     Will request the last 20 annotations for the current url.*/
                    loadFromSearch: {},
                    urls: { // These are the default URLs.
                        create: 'learning/index/save-annotation',
                        update: 'learning/index/update-annotation?id=:id',
                        destroy: 'learning/index/destroy-annotation?id=:id',
                        search: 'learning/index/search-annotations'
                    },
                }
            )
            .annotator('addPlugin', 'Unsupported',
                {message: "We're sorry the Annotator is not supported by this browser"}
            )
            .annotator('addPlugin', 'Tags')
            .annotator('addPlugin', 'Types', opt_types)
            .annotator('addPlugin', 'Classes', opt_classes)
            .annotator('addPlugin', 'Entities', opt_entities)
    }

    /**
     * Get annotations from pdf file
     * @param file
     * @param frameId
     * @param contentId
     * @param cls_progress
     */
    function getAnnotations(file, frameId, contentId, opt_types_inp, opt_classes_inp, cls_progress) {


        if ( file && typeof file != "undefined" ) {
            var extWP = file.split('.');
            var ext = extWP[extWP.length - 1];
        }

        var action;
        var opt_types;
        var opt_classes;
        var use_response = true;

        if ( frameId && typeof frameId != "undefined"  && frameId.length )
        {
            frameId = frameId;
        }else{
            frameId = "main-frame";
        }

        if ( contentId && typeof contentId != "undefined" && contentId.length ) {
            contentId = contentId;
        }else{
            contentId = "result";
        }

        if (opt_types_inp && typeof opt_types_inp != "undefined" && opt_types_inp.length) {
            opt_types = opt_types_inp;
        }else{
            opt_types = null;
        }

        if (opt_classes_inp && typeof opt_classes_inp != "undefined" && opt_classes_inp.length) {
            opt_classes = opt_classes_inp;
        } else {
            opt_classes = null;
        }

        $.getJSON( "/learning/index/get-data-user",
            function( response )
            {
                if ( ext == "pdf" )
                {
                    action = "/learning/index/convert-pdf-to-html";
                    use_response = true;
                } else if ( ext == "html" )
                {
                    action = "/learning/index/get-content-file";
                    use_response = true;
                } else {
                    action = "/learning/index/get-content-file";
                    use_response = false;
                }

                if ( use_response )
                {
                    $.post( action,
                            {
                                // Importantly OLD used - pdfFile
                                "pdfFile": file,
                            },
                            function (response) {
                                if (response.length) {
                                    showAnnotations(frameId, contentId, response, opt_types, opt_classes);
                                } else {
                                    console.log("File not found");
                                }             
                            }
                    );
                }else{
                    showAnnotations(frameId, contentId, "", opt_types, opt_classes);
                }
                removeLoader();
           
            }
        ).success(
            function( data, response ){
                
            }
        ).error(
            function ( data, xhr, textStatus, errorThrown ) {
                if ( xhr.status === 0 ) {
                    alert('Not connected. Verify Network.');
                } else if ( xhr.status == 404 ) {
                    alert('Requested page not found. [404]');
                } else if ( xhr.status == 500 ) {
                    alert('Server Error [500].');
                } else if ( errorThrown === 'parsererror' ) {
                    alert('Requested JSON parse failed.');
                } else if ( errorThrown === 'timeout' ) {
                    alert( 'Time out error.' );
                } else if ( errorThrown === 'abort' ) {
                    alert( 'Ajax request aborted.' );
                } else {
                    alert( 'Remote sever unavailable. Please try later' );
                }
            }
        ).complete(
            function( data ){

            }
        );
    }

    function showLoader( contId ) {

        if ( typeof contId != "undefined" ) {
            contId = "main-frame";
        } else {
            contId = "main-frame";
        }

        if ( !$("#" + contId).is("#loader") ) {
            $("#" + contId).prepend("<div id='loader'></div>");
            $("#loader").addClass("loader");
        }
    }

    function removeLoader() {
        $("#loader").remove();
    }

    function showCssLoader( contId ) {

        if ( typeof contId != "undefined" ) {
            contId = "main-frame";
        } else {
            contId = "main-frame";
        }

        if ( !$("#" + contId).is("#loader") ) {
            $("#" + contId).prepend(
                "<div id='loader'></div>");
            $("#loader").addClass("cssload-loader");
        }
    }

    function removeCssLoader() {
        $(".cssload-loader").remove();
    }

    function cs_how_time( beg_time_unit, end_time_unit, type )
    {
        var ins;
        var period = end_time_unit - beg_time_unit;
        //debugger;
        var second = 1000;
        var minutes = 1000 * 60;
        var hours = 60;
        var days = hours * 24;
        var years = days * 365;

        if ( type == "undefined" ) ins = second;
        else if ( type == "sec" )  ins = second;
        else if ( type == "min" )  ins = minutes;
        else if ( type == "hour" ) ins = hours;
        else if ( type == "day" )  ins = days;
        else if ( type == "year" ) ins = years;

        return parseInt(Math.round( period / ins));
    }

    /**
     * Create path to quote
     * @param el
     * @returns {*}
     */
    function xpath(el) {
        if (typeof el == "string") return document.evaluate(el, document, null, 0, null);
        if (!el || el.nodeType != 1) return '';
        if (el.id) return "//*[@id='" + el.id + "']";
        var sames = [].filter.call(el.parentNode.children, function (x) {
            return x.tagName == el.tagName
        });
        var respath = xpath(el.parentNode) + '/' + el.tagName.toLowerCase() + (sames.length > 1 ? '[' + ([].indexOf.call(sames, el) + 1) + ']' : '');

        return respath;
    }
    
    /**
     * Lookup Element By xpath
     * @param path
     * @returns {*|Node}
     */
    function lookupElementByXPath(path) {
        var evaluator = new XPathEvaluator();
        var result = evaluator.evaluate(path, document.documentElement, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null);
        return result.singleNodeValue;
    }

    /**
     * Create xpath from element
     * @param elm
     * @returns {*}
     */
    function createXPathFromElement(elm) {
        var allNodes = document.getElementsByTagName('*');
        for (var segs = []; elm && elm.nodeType == 1; elm = elm.parentNode) {
            if (elm.hasAttribute('id')) {
                var uniqueIdCount = 0;
                for (var n = 0; n < allNodes.length; n++) {
                    if (allNodes[n].hasAttribute('id') && allNodes[n].id == elm.id) uniqueIdCount++;
                    if (uniqueIdCount > 1) break;
                }
                if (uniqueIdCount == 1) {
                    segs.unshift('id("' + elm.getAttribute('id') + '")');
                    return segs.join('/');
                } else {
                    segs.unshift(elm.localName.toLowerCase() + '[@id="' + elm.getAttribute('id') + '"]');
                }
            } else if (elm.hasAttribute('class')) {
                segs.unshift(elm.localName.toLowerCase() + '[@class="' + elm.getAttribute('class') + '"]');
            } else {
                for (i = 1, sib = elm.previousSibling; sib; sib = sib.previousSibling) {
                    if (sib.localName == elm.localName)  i++;
                }
                segs.unshift(elm.localName.toLowerCase() + '[' + i + ']');
            }
        }
        return segs.length ? '/' + segs.join('/') : null;
    }

    /**
     * Get properties the current browser
     * @returns Object {
                    platform: "desktop",
                    browser: "chrome",
                    versionFull: "51.0.2681.1",
                    versionShort: "51"
                }
     */
    function getBrowser() {
        var ua = navigator.userAgent;
        var bName = function () {
            if (ua.search(/Edge/) > -1) return "edge";
            if (ua.search(/MSIE/) > -1) return "ie";
            if (ua.search(/Trident/) > -1) return "ie11";
            if (ua.search(/Firefox/) > -1) return "firefox";
            if (ua.search(/Opera/) > -1) return "opera";
            if (ua.search(/OPR/) > -1) return "operaWebkit";
            if (ua.search(/YaBrowser/) > -1) return "yabrowser";
            if (ua.search(/Chrome/) > -1) return "chrome";
            if (ua.search(/Safari/) > -1) return "safari";
            if (ua.search(/Maxthon/) > -1) return "maxthon";
        }();
        var version;
        switch (bName) {
            case "edge":
                version = (ua.split("Edge")[1]).split("/")[1];
                break;
            case "ie":
                version = (ua.split("MSIE ")[1]).split(";")[0];
                break;
            case "ie11":
                bName = "ie";
                version = (ua.split("; rv:")[1]).split(")")[0];
                break;
            case "firefox":
                version = ua.split("Firefox/")[1];
                break;
            case "opera":
                version = ua.split("Version/")[1];
                break;
            case "operaWebkit":
                bName = "opera";
                version = ua.split("OPR/")[1];
                break;
            case "yabrowser":
                version = (ua.split("YaBrowser/")[1]).split(" ")[0];
                break;
            case "chrome":
                version = (ua.split("Chrome/")[1]).split(" ")[0];
                break;
            case "safari":
                version = (ua.split("Version/")[1]).split(" ")[0];
                break;
            case "maxthon":
                version = ua.split("Maxthon/")[1];
                break;
        }
        var platform = 'desktop';
        if (/iphone|ipad|ipod|android|blackberry|mini|windows\sce|palm/i.test(navigator.userAgent.toLowerCase())) platform = 'mobile';
        var browsrObj;
            try {
                browsrObj = {
                    platform: platform,
                browser: bName,
                versionFull: version,
                versionShort: version.split(".")[0]
            };
        } catch (err) {
           browsrObj = {
                   platform: platform,
                    browser: 'unknown',
                versionFull: 'unknown',
               versionShort: 'unknown'
           };
        }
        return browsrObj;
    }

    function getClass() {
        classEntities = {
            "Decision Overview": {},
            "Proceeding Overview": {},
            "Parties in Proceeding": {
                "Party Name": {
                    "As Is": {},
                    "Alias To": ["One", "Too"]
                },
                "Party Type": {},
                "Party Position": {},
                "Is Company": {}
            }
        }
        return classEntities;
    }