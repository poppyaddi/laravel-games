webpackJsonp([45],{"+dmb":function(t,n,e){"use strict";Object.defineProperty(n,"__esModule",{value:!0});var i=e("//Fk"),a=e.n(i),o=e("Dd8w"),r=e.n(o),c=e("auNG"),s=[{title:"公告标题",dataIndex:"title",align:"center"},{title:"发布时间",dataIndex:"created_at",align:"center"},{title:"操作",key:"action",scopedSlots:{customRender:"action"},align:"center"}],l={components:{Detail:e("WcyL").default},data:function(){return{data:[],pagination:{pageSize:10},loading:!1,columns:s,title:"",filters:{}}},mounted:function(){this.fetch({pageSize:this.pagination.pageSize}),console.log(this.config)},watch:{title:function(t,n){""==t&&this.fetch({pageSize:this.pagination.pageSize})}},methods:{onSearch:function(t){var n=this;if(""==t.trim())return!1;Object(c.e)({title:t}).then(function(t){console.log(t),n.data=t.data.data;var e=r()({},n.pagination);e.total=t.data.total,n.pagination=e})},getPagination:function(){return{pageSize:this.pagination.pageSize,page:this.pagination.current,sortField:this.pagination.sortField,sortOrder:this.pagination.sortOrder}},onAdd:function(){this.fetch({pageSize:this.pagination.pageSize})},onEdit:function(){this.fetch(this.getPagination())},handleTableChange:function(t,n,e){var i=r()({},this.pagination);i.current=t.current,i.sortField=e.field,i.sortOrder=e.order,this.pagination=i,this.filters=n,this.fetch(r()({pageSize:t.pageSize,page:t.current,sortField:e.field,sortOrder:e.order},n))},fetch:function(){var t=this,n=arguments.length>0&&void 0!==arguments[0]?arguments[0]:{};this.loading=!0,Object(c.e)(n).then(function(n){console.log(n.data);var e=r()({},t.pagination),i=n.data;t.data=i.data,e.total=i.total,t.pagination=e,t.loading=!1})},del:function(t){var n=this;this.$confirm({content:"确认删除？",cancelText:"取消",okText:"删除",onOk:function(){return new a.a(function(e,i){Object(c.b)({id:t}).then(function(t){n.$message.success(t.message),n.fetch(r()({pageSize:n.pagination.pageSize,page:n.pagination.current,sortField:n.pagination.sortField,sortOrder:n.pagination.sortOrder},n.filters)),n.destroyAll()})})},onCancel:function(){n.destroyAll(),n.$message.info("取消删除",2)}})},destroyAll:function(){this.$destroyAll()}}},d={render:function(){var t=this,n=t.$createElement,e=t._self._c||n;return e("div",[e("a-row",[e("a-col",[e("a-table",{attrs:{columns:t.columns,rowKey:function(t){return t.id},dataSource:t.data,pagination:t.pagination,loading:t.loading},on:{change:t.handleTableChange},scopedSlots:t._u([{key:"parent",fn:function(n){return e("span",{},[t._v(t._s(n.title))])}},{key:"action",fn:function(t){return e("span",{},[e("detail",{attrs:{content:t.content}})],1)}}])})],1)],1),t._v(" "),e("a-divider")],1)},staticRenderFns:[]};var g=e("VU/8")(l,d,!1,function(t){e("Yyif")},null,null);n.default=g.exports},Yyif:function(t,n){}});
//# sourceMappingURL=45.7f2b2b73e703cbae0863.js.map