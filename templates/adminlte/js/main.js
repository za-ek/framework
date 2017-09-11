document.zSCRIPTS = [];

var makeUUID = (function() {
    var id = 'xxxxxxxxxxxxxxxxx'.replace(/[x]/g, function(c) {var r = Math.random()*16|0;return r.toString(16);});
    while(document.getElementById(id) != null) {
        id = makeUUID();
    }
    return id;
});

!function(e){e(["jquery"],function(e){return function(){function t(e,t,n){return g({type:O.error,iconClass:m().iconClasses.error,message:e,optionsOverride:n,title:t})}function n(t,n){return t||(t=m()),v=e("#"+t.containerId),v.length?v:(n&&(v=u(t)),v)}function i(e,t,n){return g({type:O.info,iconClass:m().iconClasses.info,message:e,optionsOverride:n,title:t})}function o(e){w=e}function s(e,t,n){return g({type:O.success,iconClass:m().iconClasses.success,message:e,optionsOverride:n,title:t})}function a(e,t,n){return g({type:O.warning,iconClass:m().iconClasses.warning,message:e,optionsOverride:n,title:t})}function r(e,t){var i=m();v||n(i),l(e,i,t)||d(i)}function c(t){var i=m();return v||n(i),t&&0===e(":focus",t).length?void h(t):void(v.children().length&&v.remove())}function d(t){for(var n=v.children(),i=n.length-1;i>=0;i--)l(e(n[i]),t)}function l(t,n,i){var o=i&&i.force?i.force:!1;return t&&(o||0===e(":focus",t).length)?(t[n.hideMethod]({duration:n.hideDuration,easing:n.hideEasing,complete:function(){h(t)}}),!0):!1}function u(t){return v=e("<div/>").attr("id",t.containerId).addClass(t.positionClass).attr("aria-live","polite").attr("role","alert"),v.appendTo(e(t.target)),v}function p(){return{tapToDismiss:!0,toastClass:"toast",containerId:"toast-container",debug:!1,showMethod:"fadeIn",showDuration:300,showEasing:"swing",onShown:void 0,hideMethod:"fadeOut",hideDuration:1e3,hideEasing:"swing",onHidden:void 0,closeMethod:!1,closeDuration:!1,closeEasing:!1,extendedTimeOut:1e3,iconClasses:{error:"toast-error",info:"toast-info",success:"toast-success",warning:"toast-warning"},iconClass:"toast-info",positionClass:"toast-top-right",timeOut:5e3,titleClass:"toast-title",messageClass:"toast-message",escapeHtml:!1,target:"body",closeHtml:'<button type="button">&times;</button>',newestOnTop:!0,preventDuplicates:!1,progressBar:!1}}function f(e){w&&w(e)}function g(t){function i(e){return null==e&&(e=""),new String(e).replace(/&/g,"&amp;").replace(/"/g,"&quot;").replace(/'/g,"&#39;").replace(/</g,"&lt;").replace(/>/g,"&gt;")}function o(){r(),d(),l(),u(),p(),c()}function s(){y.hover(b,O),!x.onclick&&x.tapToDismiss&&y.click(w),x.closeButton&&k&&k.click(function(e){e.stopPropagation?e.stopPropagation():void 0!==e.cancelBubble&&e.cancelBubble!==!0&&(e.cancelBubble=!0),w(!0)}),x.onclick&&y.click(function(e){x.onclick(e),w()})}function a(){y.hide(),y[x.showMethod]({duration:x.showDuration,easing:x.showEasing,complete:x.onShown}),x.timeOut>0&&(H=setTimeout(w,x.timeOut),q.maxHideTime=parseFloat(x.timeOut),q.hideEta=(new Date).getTime()+q.maxHideTime,x.progressBar&&(q.intervalId=setInterval(D,10)))}function r(){t.iconClass&&y.addClass(x.toastClass).addClass(E)}function c(){x.newestOnTop?v.prepend(y):v.append(y)}function d(){t.title&&(I.append(x.escapeHtml?i(t.title):t.title).addClass(x.titleClass),y.append(I))}function l(){t.message&&(M.append(x.escapeHtml?i(t.message):t.message).addClass(x.messageClass),y.append(M))}function u(){x.closeButton&&(k.addClass("toast-close-button").attr("role","button"),y.prepend(k))}function p(){x.progressBar&&(B.addClass("toast-progress"),y.prepend(B))}function g(e,t){if(e.preventDuplicates){if(t.message===C)return!0;C=t.message}return!1}function w(t){var n=t&&x.closeMethod!==!1?x.closeMethod:x.hideMethod,i=t&&x.closeDuration!==!1?x.closeDuration:x.hideDuration,o=t&&x.closeEasing!==!1?x.closeEasing:x.hideEasing;return!e(":focus",y).length||t?(clearTimeout(q.intervalId),y[n]({duration:i,easing:o,complete:function(){h(y),x.onHidden&&"hidden"!==j.state&&x.onHidden(),j.state="hidden",j.endTime=new Date,f(j)}})):void 0}function O(){(x.timeOut>0||x.extendedTimeOut>0)&&(H=setTimeout(w,x.extendedTimeOut),q.maxHideTime=parseFloat(x.extendedTimeOut),q.hideEta=(new Date).getTime()+q.maxHideTime)}function b(){clearTimeout(H),q.hideEta=0,y.stop(!0,!0)[x.showMethod]({duration:x.showDuration,easing:x.showEasing})}function D(){var e=(q.hideEta-(new Date).getTime())/q.maxHideTime*100;B.width(e+"%")}var x=m(),E=t.iconClass||x.iconClass;if("undefined"!=typeof t.optionsOverride&&(x=e.extend(x,t.optionsOverride),E=t.optionsOverride.iconClass||E),!g(x,t)){T++,v=n(x,!0);var H=null,y=e("<div/>"),I=e("<div/>"),M=e("<div/>"),B=e("<div/>"),k=e(x.closeHtml),q={intervalId:null,hideEta:null,maxHideTime:null},j={toastId:T,state:"visible",startTime:new Date,options:x,map:t};return o(),a(),s(),f(j),x.debug&&console&&console.log(j),y}}function m(){return e.extend({},p(),b.options)}function h(e){v||(v=n()),e.is(":visible")||(e.remove(),e=null,0===v.children().length&&(v.remove(),C=void 0))}var v,w,C,T=0,O={error:"error",info:"info",success:"success",warning:"warning"},b={clear:r,remove:c,error:t,getContainer:n,info:i,options:{},subscribe:o,success:s,version:"2.1.2",warning:a};return b}()})}("function"==typeof define&&define.amd?define:function(e,t){"undefined"!=typeof module&&module.exports?module.exports=t(require("jquery")):window.zNotice=t(window.jQuery)});

$.fn.animateRotate = function(angle, duration, easing, complete) {
    return this.each(function() {
        var $elem = $(this);

        $({deg: 0}).animate({deg: angle}, {
            duration: duration,
            easing: easing,
            step: function(now) {
                $elem.css({
                    transform: 'rotate(' + now + 'deg)'
                });
            },
            complete: complete || $.noop
        });
    });
};

$.fn.serializeObject = function()
{
    $(this).trigger('before_serialize');

    var o = {};
    var a = this.serializeArray();
    $.each(a, function() {
        if (o[this.name] !== undefined) {
            if (!o[this.name].push) {
                o[this.name] = [o[this.name]];
            }
            o[this.name].push(this.value || '');
        } else {
            o[this.name] = this.value || '';
        }
    });

    $(this).trigger('serialize_complete', o);
    return o;
};

$.fn.getAttributes = function(mask) {
    mask = mask.replace(/\*/, '');
    var attributes = {};

    if( this.length ) {
        $.each( this[0].attributes, function( index, attr ) {
            if ( attr.name.indexOf(mask) == 0 )
                attributes[ attr.name ] = attr.value;
        } );
    }

    return attributes;
};

var renderLoading = (function(d) {
    if ( d ) {
        $('#loading-global').data('loading_count', $('#loading-global').data('loading_count') + d)
    }
    if ( $('#loading-global').data('loading_count') > 0 ) {
        $('#loading-global').show();
    } else {
        $('#loading-global').hide();
    }
});

var zAjax_collection = {};

var fixed_box = (function() {
    $('.fixed-box').each(function() {
        var max_height = $(window).height() - $('.main-header').height() - $('.content-header').height() - 45;
        $(this).css('max-height', max_height);
    });
    $('section.content').each(function() {
        var max_height = $(window).height() - $('.main-header').height() - $('.content-header').height() - 31;
        $(this).css('height', max_height);
    });
});

var zAjax_finish = (function(func, a,b,c) {
    $('#' + a.widget_id).closest('.box').find('.overlay').hide();

    if ( func ) {
        var el = func(a,b,c);
        if ( a.widget_id ) {
            $('#' + a.widget_id).data('zAjax', a)
                .trigger('zAjax:complete', [a,b,c])
                .trigger('zAjax_complete:' + a.widget_id, [a,b,c]);
        }
    } else {
        if ( a.widget_id ) {
            $('#' + a.widget_id)
                .data('zAjax', a)
                .html(a.result)
                .trigger('zAjax:complete', [a,b,c]);
            $(window).trigger('zAjax_complete:' + a.widget_id, [a,b,c]);
        }
    }

    if ( a.widget_id ) {
        $('#' + a.widget_id).find('*').filter(function() {
            return $(this).attr('data-intro');
        }).each(function() {
            $(this).attr('title', $(this).attr('data-intro'));
        });
    }
    fixed_box();

    if ( a.widget_id ) {
        $(a.widget_id).trigger('zAjax:complete');
    }

    $(window).trigger('zAjax_finish-' + a.act, ['#' + a.widget_id, a,b,c]);
});

var zAjax = (function(d,func) {
    if ( typeof d == 'string' ) {
        d = {url:d};
    }

    if ( d.widget_id ) {
        zAjax_collection[d.widget_id] = [d,func]
    }

    d.zAjax = "1";
    d.zAjaxType = "json";
    renderLoading(1);

    return $.ajax({
        url : d.url,
        dataType:'json',
        data : d,
        method: 'post',
        error:function(e,b,x){
            renderLoading(-1);
            console.log(e,b,x);
            zNotice['error'](b);
        },
        success:function(a,b,c) {
            var d=document;
            if ( a.page_param.css.length > 0 ) {
                loop_css:while(src = a.page_param.css.shift()) {
                    var cl = d.getElementsByTagName('link');
                    for ( var i in cl ) {if ( cl[i].href == src ) continue loop_css;}
                    $("head").append("<link rel='stylesheet' type='text/css' href='"+src+"' />");
                }
            }
            var need_wait = false;
            if (a.page_param.js.length > 0) {
                var scripts = a.page_param.js;
                document.zLOADING = 0;
                loop_js : while (src = scripts.shift()) {
                    var sl = document.getElementsByTagName('script');
                    if ( document.zSCRIPTS.indexOf(src) == -1 ) {
                        for (var i in sl) {
                            if (sl[i].src == src) {
                                continue loop_js;
                            }
                        }
                        document.zLOADING++;
                        need_wait = true;
                        document.zSCRIPTS.push(src);
                        $.getScript(src, function (q) {
                            console.log('finish loading');
                            document.zLOADING--;
                            if (document.zLOADING == 0) {
                                zAjax_finish(func, a, b, c);
                                renderLoading(-1);
                            }
                        });

                    }
                }
            }
            if ( !need_wait ) {
                if ( a.error ) {
                    zNotice['warning'](a.error_original);
                    console.log(a);
                } else {
                    /** STATE */
                    if ( a.widget_id && a.target_url ) {
                        var state = window.history.state;
                        if ( state ) {
                            if (!state.zAjax) {
                                state.zAjax = {};
                            }
                        } else {
                            state = {zAjax: {}};
                        }
                        state.zAjax[a.widget_id] = a.result;
                        var url;

                        if ( a.target_url && typeof ( a.target_url ) == 'object' ) {
                            url = document.location.pathname;
                            for ( var x in a.target_url ) {
                                key = encodeURI(x);
                                value = encodeURI(a.target_url[x]);
                                var kvp = document.location.search.substr(1).split('&');
                                var i = kvp.length, x;
                                while(i--) {
                                    x = kvp[i].split('=');
                                    if (x[0]==key) {
                                        x[1] = value;
                                        kvp[i] = x.join('=');
                                        break;
                                    }
                                }
                                if(i<0) {
                                    kvp[kvp.length] = [key,value].join('=');
                                }
                                url += '?' + kvp.join('&');
                            }
                        } else if (a.target_url) {
                            url = a.target_url;
                        } else {
                            url = document.location;
                        }
                        window.history.pushState(state, a.page_param.title, url);
                    }
                    /** STATE */
                }

                zAjax_finish(func, a, b, c);

                renderLoading(-1);
            } else {
                // zAjax_finish(func, a, b, c);
            }
        }
    });
});

(function( $ ){
    $.fn.zAjax = function(d, func) {

        if ( !$(this).attr('id') ) {
            $(this).attr('id', makeUUID());
        }

        d.widget_id = $(this).attr('id');

        if ( $(this).closest('.box').find('.overlay').length ) {
            $(this).closest('.box').find('.overlay').show();
        }

        return zAjax(d, func);
    };
})( jQuery );