webpackJsonp([57],{"22IA":function(i,t,e){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var n={components:{ConfirmForm:e("OXv5").default},props:{id:{type:Number,default:null},disabled:{type:Boolean,default:!1}},data:function(){return{visible:!1}},methods:{showConfirm:function(){var i=this;this.$confirm({title:"一键转移库存至主账户?",content:"转移后可再次从主账户分配至子账户",cancelText:"取消",okText:"转移",onOk:function(){i.visible=!0},onCancel:function(){}})},showModal:function(){this.visible=!0},handleOk:function(i){console.log(i),this.visible=!1},onDist:function(){this.visible=!1,this.$emit("dist")}}},l={render:function(){var i=this,t=i.$createElement,e=i._self._c||t;return e("div",{staticStyle:{display:"inline"}},[e("a-button",{attrs:{size:"small",disabled:i.disabled,type:"primary"},on:{click:i.showConfirm}},[i._v("转主")]),i._v(" "),e("a-modal",{attrs:{title:"请输入支付密码",footer:null},on:{ok:i.handleOk},model:{value:i.visible,callback:function(t){i.visible=t},expression:"visible"}},[e("confirm-form",{attrs:{id:i.id},on:{dist:i.onDist}})],1)],1)},staticRenderFns:[]},o=e("VU/8")(n,l,!1,null,null,null);t.default=o.exports}});
//# sourceMappingURL=57.ae2f7e0fcab741368035.js.map