webpackJsonp([53],{kKyb:function(t,e,n){"use strict";Object.defineProperty(e,"__esModule",{value:!0});var o={components:{FormComponent:n("A3Ic").default},props:{text:{type:Object,default:null}},data:function(){return{ModalText:"Content of the modal",visible:!1,confirmLoading:!1}},methods:{showModal:function(){this.visible=!0,console.log(this.text)},handleOk:function(t){var e=this;this.ModalText="The modal will be closed after two seconds",this.confirmLoading=!0,setTimeout(function(){e.visible=!1,e.confirmLoading=!1},2e3)},handleCancel:function(t){console.log("Clicked cancel button"),this.visible=!1},onDist:function(){this.$emit("dist")}}},i={render:function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("div",{staticStyle:{display:"inline"}},[n("a-button",{attrs:{type:"primary",size:"small"},on:{click:t.showModal}},[t._v("分配")]),t._v(" "),n("a-modal",{attrs:{title:"分配库存",visible:t.visible,confirmLoading:t.confirmLoading,width:"42%",footer:null},on:{ok:t.handleOk,cancel:t.handleCancel}},[n("form-component",{attrs:{text:t.text},on:{cancel:function(e){t.visible=!1},dist:t.onDist}})],1)],1)},staticRenderFns:[]},l=n("VU/8")(o,i,!1,null,null,null);e.default=l.exports}});
//# sourceMappingURL=53.c6a391460fc6b572f25d.js.map