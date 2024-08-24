(()=>{"use strict";const e=window.React,t=window.wp.i18n,i=window.wp.hooks,o=window.wp.element,a=window.wp.blockEditor,l=window.wp.components,n=window.wp.compose,s=window.wp.data,r=["core/image"],c=(0,n.createHigherOrderComponent)((i=>n=>{if(!r.includes(n.name))return(0,e.createElement)(i,{...n});const{attributes:s,setAttributes:c,isSelected:d}=n,{width:p,height:m,url:u,aspectRatio:w,focalPoint:h}=s,[g,y]=(w||"/").split("/"),[b,E]=(0,e.useState)(h),x=p&&"auto"!==p&&m;return(0,e.createElement)(o.Fragment,null,(0,e.createElement)(i,{...n}),d&&w&&(0,e.createElement)(a.InspectorControls,null,(0,e.createElement)(l.PanelBody,{className:"aspect-ratio-settings"},(0,e.createElement)("p",{className:"label"},(0,t.__)("Aspect ratio")),(0,e.createElement)(l.Flex,{style:{paddingBottom:"8px"}},(0,e.createElement)(l.FlexItem,null,(0,e.createElement)(l.__experimentalNumberControl,{placeholder:(0,t.__)("Width"),value:g,disabled:x,spinControls:"none",onChange:e=>{c({aspectRatio:`${e}/${y}`})}})),(0,e.createElement)(l.FlexItem,{style:{paddingBottom:"8px"}},"/"),(0,e.createElement)(l.FlexItem,null,(0,e.createElement)(l.__experimentalNumberControl,{placeholder:(0,t.__)("Height"),value:y,disabled:x,spinControls:"none",onChange:e=>{c({aspectRatio:`${g}/${e}`})}}))),(0,e.createElement)(l.FocalPointPicker,{__nextHasNoMarginBottom:!0,label:(0,t.__)("Set focal point","aspect-ratio"),url:u,value:h,onDragStart:e=>{c({focalPoint:e}),E(e)},onDrag:e=>{c({focalPoint:e}),E(e)},onChange:e=>{c({focalPoint:e}),E(e)}}))))}),"withAdvancedControls");(0,i.addFilter)("blocks.registerBlockType","utilities/aspect-ratio",(function(e,t){return r.includes(t)&&(e={...e,attributes:{...e.attributes,focalPoint:{type:"object",default:{x:.5,y:.5}}}}),e})),(0,i.addFilter)("editor.BlockEdit","utilities/aspect-ratio-control",c),(0,i.addFilter)("blocks.getSaveContent.extraProps","utilities/applyAspectRatioAttribute",((e,t,i)=>{if(r.includes(t.name)){const{width:t,height:o,aspectRatio:a,focalPoint:l}=i;a&&(t&&"auto"!==t&&o||.5===l.x&&.5===l.y||(e={...e,"data-object-position":`${100*l.x}% ${100*l.y}%`}))}return e}));const d=(0,n.createHigherOrderComponent)((t=>i=>{if(r.includes(i.name)){const e=document.querySelector(`#block-${i.clientId} img`);if(e){const{width:t,height:o,aspectRatio:a,scale:l,focalPoint:n}=i.attributes;e.style.aspectRatio="",e.style.objectFit="",e.style.objectPosition="",e.style.width="",e.style.height="",e.parentElement.style.height="",e.nextElementSibling&&(e.nextElementSibling.style.display=""),a&&(t&&"auto"!==t&&o||window.requestAnimationFrame((()=>{e.style.aspectRatio=a,e.style.objectFit=l||"cover",e.style.objectPosition=`${100*n.x}% ${100*n.y}%`,"auto"!==t?(e.style.width=t||"100%",e.style.height="auto",e.parentElement.style.height="auto"):(e.style.width="",e.parentElement.style.width=""),e.nextElementSibling&&(e.nextElementSibling.style.display="none")})))}}return(0,e.createElement)(t,{...i})}),"aspectRatioPreview");wp.hooks.addFilter("editor.BlockListBlock","utilities/aspect-ratio-preview",d),(0,i.addFilter)("blockEditor.useSetting.before","utilities/aspect-ratio-select",((e,i,o,a)=>{if("core/image"===a&&"dimensions.aspectRatios.theme"===i){const i=(0,s.select)("core/block-editor").getBlockAttributes(o);e=[{name:(0,t.__)("Manually","aspect-ratio"),slug:"manually",ratio:i.aspectRatio||"/"}]}return e}))})();