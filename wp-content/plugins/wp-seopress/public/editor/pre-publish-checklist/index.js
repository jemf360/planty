(()=>{"use strict";const e=window.wp.components,t=window.wp.data,r=window.wp.editPost,n=window.wp.i18n,o=window.wp.plugins,s=window.wp.wordcount;function c(e,t){var r=Object.keys(e);if(Object.getOwnPropertySymbols){var n=Object.getOwnPropertySymbols(e);t&&(n=n.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),r.push.apply(r,n)}return r}function l(e){for(var t=1;t<arguments.length;t++){var r=null!=arguments[t]?arguments[t]:{};t%2?c(Object(r),!0).forEach((function(t){i(e,t,r[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(r)):c(Object(r)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(r,t))}))}return e}function i(e,t,r){return t in e?Object.defineProperty(e,t,{value:r,enumerable:!0,configurable:!0,writable:!0}):e[t]=r,e}function a(e,t){(null==t||t>e.length)&&(t=e.length);for(var r=0,n=new Array(t);r<t;r++)n[r]=e[r];return n}for(var u={metaTitle:{selector:"#seopress_titles_title_meta",type:"text",key:"_seopress_titles_title"},metaDescription:{selector:"#seopress_titles_desc_meta",type:"text",key:"_seopress_titles_desc"},metaIndex:{selector:"#seopress_robots_index_meta",type:"checkbox",key:"_seopress_robots_index"}},p=function(){var e,r,n=(e=m[d],r=2,function(e){if(Array.isArray(e))return e}(e)||function(e,t){var r=null==e?null:"undefined"!=typeof Symbol&&e[Symbol.iterator]||e["@@iterator"];if(null!=r){var n,o,s=[],_n=!0,c=!1;try{for(r=r.call(e);!(_n=(n=r.next()).done)&&(s.push(n.value),!t||s.length!==t);_n=!0);}catch(e){c=!0,o=e}finally{try{_n||null==r.return||r.return()}finally{if(c)throw o}}return s}}(e,r)||function(e,t){if(e){if("string"==typeof e)return a(e,t);var r=Object.prototype.toString.call(e).slice(8,-1);return"Object"===r&&e.constructor&&(r=e.constructor.name),"Map"===r||"Set"===r?Array.from(e):"Arguments"===r||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(r)?a(e,t):void 0}}(e,r)||function(){throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()),o=(n[0],n[1]);document.querySelector(o.selector).addEventListener("change",(function(e){o.value="checkbox"==o.type?e.target.checked:e.target.value;var r=(0,t.select)("core/editor").getEditedPostAttribute("meta");(0,t.dispatch)("core/editor").editPost({meta:l(l({},r),{},i({},o.key,o.value))})}))},d=0,m=Object.entries(u);d<m.length;d++)p();(0,o.registerPlugin)("pre-publish-checklist",{render:function(){var o=(0,t.useSelect)((function(e){return{content:e("core/editor").getEditedPostAttribute("content"),meta:e("core/editor").getEditedPostAttribute("meta")}})),c=o.content,l=o.meta,i=(0,s.count)(c,"words"),a=u.metaTitle.value||l._seopress_titles_title||"",p=u.metaDescription.value||l._seopress_titles_desc||"",d=u.metaIndex.value||l._seopress_robots_index||!1,m=React.createElement(e.Icon,{icon:"yes",style:{color:"green"}}),_=React.createElement(e.Icon,{icon:"no",style:{color:"red"}});return React.createElement(r.PluginPrePublishPanel,{title:(0,n.__)("SEO Checklist","wp-seopress")},React.createElement("ul",null,React.createElement("li",null,i>10?m:_,React.createElement("strong",null,(0,n.__)("Wordcount: ","wp-seopress")),React.createElement("span",null,i)),React.createElement("li",null,a?m:_,React.createElement("strong",null,a?(0,n.__)("Meta title is set.","wp-seopress"):(0,n.__)("Meta title is not set.","wp-seopress"))),React.createElement("li",null,p?m:_,React.createElement("strong",null,p?(0,n.__)("Meta description is set.","wp-seopress"):(0,n.__)("Meta description is not set.","wp-seopress"))),React.createElement("li",null,d?_:m,React.createElement("strong",null,d?(0,n.__)("Post is set to noindex.","wp-seopress"):(0,n.__)("Post is set to be indexed.","wp-seopress")))))}})})();