webpackJsonp([85],{C2mx:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var r=a("T452"),s={props:{onAdd:{type:Function,default:null}},data:function(){return{visible:!1,confirmLoading:!1,formLayout:"horizontal",form:this.$form.createForm(this,{name:"coordinated"}),roles:[]}},methods:{showModal:function(){this.visible=!0},handleCancel:function(e){console.log("Clicked cancel button"),this.visible=!1,this.form.resetFields()},handleSubmit:function(e){var t=this;e.preventDefault(),this.form.validateFields(function(e,a){e||(t.confirmLoading=!0,Object(r.a)(a).then(function(e){t.onAdd(),t.$message.success(e.message),t.visible=!1,t.confirmLoading=!1,t.form.resetFields()}))})}}},l={render:function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("div",[a("a-button",{style:{marginTop:"4px"},attrs:{size:"small",type:"primary"},on:{click:e.showModal}},[e._t("default",[a("a-icon",{attrs:{type:"plus"}}),e._v("添加\n    ")])],2),e._v(" "),a("a-modal",{attrs:{title:"配置添加",visible:e.visible,confirmLoading:e.confirmLoading,footer:null,width:400},on:{cancel:e.handleCancel}},[a("a-form",{attrs:{form:e.form},on:{submit:e.handleSubmit}},[a("a-form-item",{attrs:{label:"键名","label-col":{span:6},"wrapper-col":{span:18}}},[a("a-input",{directives:[{name:"decorator",rawName:"v-decorator",value:["key",{rules:[{required:!0,message:"键名必须填写"}]}],expression:"['key', { rules: [{ required: true, message: '键名必须填写' }] }]"}],attrs:{placeholder:"请输入键名"}})],1),e._v(" "),a("a-form-item",{attrs:{label:"键值","label-col":{span:6},"wrapper-col":{span:18}}},[a("a-input",{directives:[{name:"decorator",rawName:"v-decorator",value:["value",{rules:[{required:!0,message:"键值必须填写"}]}],expression:"['value', { rules: [{ required: true, message: '键值必须填写' }] }]"}],attrs:{placeholder:"请输入键值"}})],1),e._v(" "),a("a-form-item",{attrs:{label:"配置描述","label-col":{span:6},"wrapper-col":{span:18}}},[a("a-textarea",{directives:[{name:"decorator",rawName:"v-decorator",value:["description",{rules:[{required:!0,message:"描述必须填写"}]}],expression:"['description', { rules: [{ required: true, message: '描述必须填写' }] }]"}],attrs:{placeholder:"请输入键值描述"}})],1),e._v(" "),a("a-form-item",{attrs:{"wrapper-col":{span:8,offset:15}}},[a("a-row",[a("a-col",{style:{textAlign:"left"},attrs:{span:12}},[a("a-button",{attrs:{size:"small"},on:{click:e.handleCancel}},[e._v("返回")])],1),e._v(" "),a("a-col",{style:{textAlign:"left"},attrs:{span:8}},[a("a-button",{attrs:{size:"small",type:"primary","html-type":"submit"}},[e._v("提交")])],1)],1)],1)],1)],1)],1)},staticRenderFns:[]},o=a("VU/8")(s,l,!1,null,null,null);t.default=o.exports}});
//# sourceMappingURL=85.fad54d5fb52fb63e7d67.js.map