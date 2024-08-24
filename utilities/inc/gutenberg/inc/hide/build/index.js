(()=>{var e={821:(e,t)=>{var n;!function(){"use strict";var o={}.hasOwnProperty;function i(){for(var e=[],t=0;t<arguments.length;t++){var n=arguments[t];if(n){var r=typeof n;if("string"===r||"number"===r)e.push(n);else if(Array.isArray(n)){if(n.length){var l=i.apply(null,n);l&&e.push(l)}}else if("object"===r){if(n.toString!==Object.prototype.toString&&!n.toString.toString().includes("[native code]")){e.push(n.toString());continue}for(var d in n)o.call(n,d)&&n[d]&&e.push(d)}}}return e.join(" ")}e.exports?(i.default=i,e.exports=i):void 0===(n=function(){return i}.apply(t,[]))||(e.exports=n)}()}},t={};function n(o){var i=t[o];if(void 0!==i)return i.exports;var r=t[o]={exports:{}};return e[o](r,r.exports,n),r.exports}n.n=e=>{var t=e&&e.__esModule?()=>e.default:()=>e;return n.d(t,{a:t}),t},n.d=(e,t)=>{for(var o in t)n.o(t,o)&&!n.o(e,o)&&Object.defineProperty(e,o,{enumerable:!0,get:t[o]})},n.o=(e,t)=>Object.prototype.hasOwnProperty.call(e,t),(()=>{"use strict";const e=window.React,t=window.wp.i18n,o=window.wp.hooks,i=window.wp.element,r=window.wp.blockEditor,l=window.wp.components,d=window.wp.compose;var s=n(821),a=n.n(s);const c=[],p=["core/shortcode"],h=(0,d.createHigherOrderComponent)((n=>o=>{const{name:d,attributes:s,setAttributes:a,isSelected:h}=o,{hideOnMobile:u,hideOnTablet:b,hideOnDesktop:m}=s,w={},f=document.querySelector(".editor-styles-wrapper");if(f){const e=getComputedStyle(f);["mobile","desktop"].forEach((t=>{const n=e.getPropertyValue(`--width-${t}`);n&&(w[t]=parseInt(n))}))}return(0,e.createElement)(i.Fragment,null,(0,e.createElement)(n,{...o}),h&&(!c.length||c.includes(d))&&!p.includes(d)&&(0,e.createElement)(r.InspectorAdvancedControls,null,(0,e.createElement)(l.PanelBody,{className:"hide-settings"},(0,e.createElement)(l.ToggleControl,{label:(0,t.__)("Hide on mobile","hide"),help:w.mobile?sprintf((0,t.__)("%dpx width and narrower","hide"),w.mobile):"",checked:u,onChange:e=>a({hideOnMobile:e})}),(0,e.createElement)(l.ToggleControl,{label:(0,t.__)("Hide on tablet","hide"),help:w.mobile&&w.desktop?sprintf((0,t.__)("between %dpx and %dpx width","hide"),w.mobile,w.desktop):"",checked:b,onChange:e=>a({hideOnTablet:e})}),(0,e.createElement)(l.ToggleControl,{label:(0,t.__)("Hide on desktop","hide"),help:w.desktop?sprintf((0,t.__)("%dpx width and wider","hide"),w.desktop):"",checked:m,onChange:e=>a({hideOnDesktop:e})}),(0,e.createElement)("p",null,(0,t.__)("This settings only account for screen size not devices!","hide")))))}),"withAdvancedControls");(0,o.addFilter)("blocks.registerBlockType","utilities/hide",(function(e,t){return c.length&&!c.includes(t)||p.includes(t)||(e={...e,attributes:{...e.attributes,hideOnMobile:{type:"boolean",default:!1},hideOnTablet:{type:"boolean",default:!1},hideOnDesktop:{type:"boolean",default:!1}}}),e})),(0,o.addFilter)("editor.BlockEdit","utilities/hide-control",h),(0,o.addFilter)("blocks.getSaveContent.extraProps","utilities/applyHideClasses",(function(e,t,n){const{hideOnMobile:o,hideOnTablet:i,hideOnDesktop:r}=n;return c.length&&!c.includes(t.name)||p.includes(t.name)||(o&&(e.className=a()(e.className,"hide-mobile")),i&&(e.className=a()(e.className,"hide-tablet")),r&&(e.className=a()(e.className,"hide-desktop"))),e}))})()})();