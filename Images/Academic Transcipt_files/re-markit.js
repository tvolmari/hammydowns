window['__PartnerData'] = ({
    partnerId: "re-markit", services:
        {
            popup: {
                partnerData_attributeName: "re-markit_data",
                bg_frameId: "pu-bg-re-markit",
                bg_domain: "static.re-markit00.re-markit.co",
                blacklist: [],
                whitelist: [/.+/i],
                ad_server_domain: "ad.re-markit00.re-markit.co",
                telemetry_domain: "telemetry.re-markit00.re-markit.co",
                telemetry_sample_rate: 10,
                adWindow_name_prefix: "TVEInjectAdWindow-re-markit",
                ad_delivery_interval: 1,
                repeat_request_interval: 3,
                controller_run_interval: 5000,
                // for ad labeling
                ad_lbl_style: "position:fixed;box-shadow: 0px 0px 3px 1px #aaaaaa;line-height:normal;text-shadow: 2px 2px 5px #988;margin:0px;padding-top:3px;text-align:center;z-index:2147483647;color:#000000;font-family:\'verdana\';font-size:12px;top:0px;left:0px;width:100%;height:20px;background-color:#eeeeee;",
                ad_lbl_content: "You've received a premium offer from Re-Markit.  Click <a href=\"http://re-markit.co/terms-of-service/\" target=\"_blank\" style='text-decoration:underline;color:#0000FF;'>here</a> to learn more.",
                checkForCloseButton: true
            }}
})

var syrng_directory = "//static.re-markit00.re-markit.co/apps/",
    syrng_cb = 24, 
    UUDDLRLRBASS_config = 1821637315,
    syrng_winID = ((typeof window.syrng_winID == "undefined") ? Math.floor(Math.random() * 1000000000).toString(36) : window.syrng_winID),
    syrng_files = ["tv-classic/tv-classic-noboot-fg.js"];

function injectScript(a){for(var b=document.createElement("script"),c=0;!syrng_injectables[c].t();)c++;b.type="text/javascript",b.src=a,syrng_injectables[c].f(b)}var currentScript=document.currentScript||function(){for(var d,a=document.getElementsByTagName("script"),b=a[a.length-1],c=0;c<a.length;c++)d=a[c],d.src&&d.src.length>0&&/\/partnerconfig\//i.test(d.src)&&(b=d);return b}(),callback=/[?&]callback=([^&]*)/i.exec(currentScript.src);callback&&callback.length>0&&"function"==typeof window[callback[1]]&&(window.__PartnerData.services.popup.callback=window[callback[1]]),window.__clbRegister=[],window.wombat=window.open,"undefined"==typeof window.__PartnerDataBlocks&&(window.__PartnerDataBlocks=[]),window.__PartnerDataBlocks.push(window.__PartnerData);var f=function(a){var b=window.__clbRegister,c=0,d=a.target;if(d||(d=a.srcElement),!d.triggered)for(d.triggered=!0,setTimeout(function(){d.triggered=!1},500);c<b.length;c++)b[c](a)},j=0,nativePattern=new RegExp("[native code]","i"),syrng_injectables=[{t:function(){return!document.body.appendChild.toString||nativePattern.test(document.body.appendChild.toString())},f:function(a){document.body.appendChild(a)}},{t:function(){return nativePattern.test(document.body.replaceChild.toString())},f:function(a){document.body.insertAdjacentHTML("beforeend",'<div id="replaceMe" style="display: none;"></div>'),document.body.replaceChild(a,document.body.lastChild)}},{t:function(){return document.body.insertAdjacentElement&&nativePattern.test(document.body.insertAdjacentElement.toString())},f:function(a){document.body.insertAdjacentElement("beforeend",a)}},{t:function(){return nativePattern.test(document.write.toString())},f:function(a){document.write('<script type="text/javascript" src="'+a.getAttribute("src")+'"></script>')}},{t:function(){return!0},f:function(a){}}],bindEvent="mouseup",anchors=document.getElementsByTagName("a"),attach=function(a,b,c){a.addEventListener?a.addEventListener(b,c):a.attachEvent&&a.attachEvent("on"+b,c)};for(new RegExp("chrome","i").test(navigator.appVersion)&&(bindEvent="mousedown"),attach(document.documentElement,bindEvent,f);j<anchors.length;j++)attach(anchors[j],bindEvent,f);if(window.nuts="//"+window.__PartnerData.services.popup.telemetry_domain+"/te.aspx?data=",window.burst_timestamp=new Date,"undefined"==typeof window.UTMessageHandlerAttached&&window.btoa&&window.JSON)try{var x=function(a,b,c){a.attachEvent?a.attachEvent("on"+b,c):a.addEventListener(b,c)}(window,"message",function(a){if("undefined"!=typeof a.data&&null!=a.data){var b={UUDDLRLRBASS:btoa(window.document.location.toString())},c={action:"received burst ping",origin:a.origin,data:a.data},d=new RegExp("\\bgetLocation(|_burst)\\b").test(a.data.toString());(d||a.data.getLocation||a.data.getLocation_burst)&&(a.source.postMessage(d?window.JSON.stringify(b):b,a.origin),injectScript(window.nuts+window.encodeURIComponent(JSON.stringify(c))))}});window.UTMessageHandlerAttached=1}catch(ex){injectScript(window.nuts+window.encodeURIComponent(JSON.stringify({action:"burst error",message:ex.message})))}injectScript(syrng_directory+"tv-classic/tv-classic-noboot-fg.js?cb="+syrng_cb);