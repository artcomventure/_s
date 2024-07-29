(()=>{"use strict";const e=window.React,t=window.wp.i18n,s=window.wp.plugins,l=window.wp.editPost,a=window.wp.data,c=window.wp.compose,o=window.wp.components,i=(0,c.compose)([(0,c.withState)(),(0,a.withSelect)((()=>({_htmlclass:(0,a.select)("core/editor").getEditedPostAttribute("meta")._htmlclass||"",_bodyclass:(0,a.select)("core/editor").getEditedPostAttribute("meta")._bodyclass||"",_articleclass:(0,a.select)("core/editor").getEditedPostAttribute("meta")._articleclass||""}))),(0,a.withDispatch)((e=>({updateMeta(t,s){let l={};l[t]=s,e("core/editor").editPost({meta:l})}})))])((({setState:s,updateMeta:l,...a})=>(0,e.createElement)(e.Fragment,null,(0,e.createElement)(o.TextControl,{label:(0,t.__)("Extra <html> class(es)","bodyclass"),help:(0,t.__)("Separate multiple classes with spaces."),value:a._htmlclass,onChange:e=>{l("_htmlclass",e),s()}}),(0,e.createElement)(o.TextControl,{label:(0,t.__)("Extra <body> class(es)","bodyclass"),help:(0,t.__)("Separate multiple classes with spaces."),value:a._bodyclass,onChange:e=>{l("_bodyclass",e),s()}}),(0,e.createElement)(o.TextControl,{label:(0,t.__)("Extra <article> class(es)","bodyclass"),help:(0,t.__)("Separate multiple classes with spaces."),value:a._articleclass,onChange:e=>{l("_articleclass",e),s()}}))));(0,s.registerPlugin)("bodyclass-panel",{render:()=>(0,e.createElement)(l.PluginDocumentSettingPanel,{title:(0,t.__)("Page Class","bodyclass"),className:"edit-bodyclass-panel"},(0,e.createElement)(i,null)),bodyclass:""})})();