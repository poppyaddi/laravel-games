webpackJsonp([63],{"/5YQ":function(e,s){},Bgvw:function(e,s,t){"use strict";Object.defineProperty(s,"__esModule",{value:!0});var n=t("X2Oc"),i={path:"/user",name:"User",component:function(){return Promise.all([t.e(0),t.e(1)]).then(t.bind(null,"4er+"))},redirect:"/user/list",children:[Object(n.a)("/user/list","/system/users/UserList","UserList","用户列表"),Object(n.a)("/user/add","/system/users/UserAdd","UserAdd","用户添加"),Object(n.a)("/user/edit","/system/users/UserEdit","UserEdit","用户修改")]};s.default=i},GiYn:function(e,s,t){"use strict";Object.defineProperty(s,"__esModule",{value:!0});var n=t("X2Oc"),i={path:"/device",name:"Device",component:function(){return Promise.all([t.e(0),t.e(1)]).then(t.bind(null,"4er+"))},redirect:"/device/list",children:[Object(n.a)("/device/list","/device/devices/DeviceList","DeviceList","设备列表")]};s.default=i},HBV9:function(e,s,t){"use strict";Object.defineProperty(s,"__esModule",{value:!0});var n=t("X2Oc"),i={path:"/statistic",name:"Statistic",component:function(){return Promise.all([t.e(0),t.e(1)]).then(t.bind(null,"4er+"))},redirect:"/statistic/list",children:[Object(n.a)("/statistic/list","/stock/statistics/StatisticList","StatisticList","数据统计")]};s.default=i},ISR7:function(e,s,t){"use strict";Object.defineProperty(s,"__esModule",{value:!0});var n=t("X2Oc"),i={path:"/migration",name:"Migration",component:function(){return Promise.all([t.e(0),t.e(1)]).then(t.bind(null,"4er+"))},redirect:"/migration/list",children:[Object(n.a)("/migration/list","/stock/migrations/MigrationList","MigrationList","凭证迁移")]};s.default=i},IcnI:function(e,s,t){"use strict";var n=t("7+uW"),i=t("NYxO"),r={token:function(e){return e.user.token},expires_in:function(e){return e.user.expires_in},element:function(e){return e.user.element}};n.a.use(i.a);var o=t("w+hY"),c=o.keys().reduce(function(e,s){var t=s.replace(/^\.\/(.*)\.\w+$/,"$1"),n=o(s);return e[t]=n.default,e},{}),a=new i.a.Store({modules:c,getters:r});s.a=a},KsCx:function(e,s,t){"use strict";Object.defineProperty(s,"__esModule",{value:!0});var n=t("X2Oc"),i={path:"/role",name:"Role",component:function(){return Promise.all([t.e(0),t.e(1)]).then(t.bind(null,"4er+"))},redirect:"/role/list",children:[Object(n.a)("/role/list","/system/roles/RoleList","RoleList","角色列表"),Object(n.a)("/role/add","/system/roles/RoleAdd","RoleAdd","角色添加"),Object(n.a)("/role/edit","/system/roles/RoleEdit","RoleEdit","角色修改")]};s.default=i},KtAA:function(e,s,t){"use strict";Object.defineProperty(s,"__esModule",{value:!0});var n=t("X2Oc"),i={path:"/game",name:"Game",component:function(){return Promise.all([t.e(0),t.e(1)]).then(t.bind(null,"4er+"))},redirect:"/game/list",children:[Object(n.a)("/game/list","/game/games/GameList","GameList","游戏列表"),Object(n.a)("/game/add","/game/games/GameAdd","GameAdd","游戏添加")]};s.default=i},NHnr:function(e,s,t){"use strict";Object.defineProperty(s,"__esModule",{value:!0});var n=t("7+uW"),i={render:function(){var e=this.$createElement,s=this._self._c||e;return s("div",{attrs:{id:"app"}},[s("router-view")],1)},staticRenderFns:[]};var r=t("VU/8")({name:"App"},i,!1,function(e){t("/5YQ")},null,null).exports,o=t("/ocq"),c=t("IcnI"),a=t("X2Oc");n.a.use(o.a);var u=t("kyiS"),l=u.keys().reduce(function(e,s){var t=s.replace(/^\.\/(.*)\.\w+$/,"$1"),n=u(s);return e[t]=n.default,e},{}),d=l.config,m=l.role,v=l.menu,p=l.perm,f=[l.user,m,v,p,d,l.userinfo,l.son,l.game,l.price,l.device,l.stock,l.ins,l.out,l.statistic,l.dist,l.migration,{path:"/login",component:function(){return t.e(2).then(t.bind(null,"T+/8"))},name:"Login",beforeEnter:function(e,s,t){c.a.getters.token?t({path:s.fullPath,query:{redirect:s.fullPath}}):t()}},{path:"/",component:function(){return Promise.all([t.e(0),t.e(1)]).then(t.bind(null,"4er+"))},redirect:"/dashboard",children:[Object(a.a)("/dashboard","/Dashboard","Dashboard")]},{path:"/404",component:function(){return Promise.all([t.e(0),t.e(1)]).then(t.bind(null,"4er+"))},redirect:"/404",children:[Object(a.a)("/404","/public/404","404")]},{path:"/401",component:function(){return Promise.all([t.e(0),t.e(1)]).then(t.bind(null,"4er+"))},redirect:"/401",children:[Object(a.a)("/401","/public/401","401")]}],j=new o.a({routes:f});j.beforeEach(function(e,s,t){e.meta.requireAuth?c.a.getters.token?t():t({path:"/login",query:{redirect:e.fullPath}}):t()});var b=j,g=t("PJh5"),w=t.n(g),h=(t("Vz2w"),t("2vhu"));t("hZ/y");w.a.locale("zh-cn"),n.a.use(h.b),n.a.prototype.hasPermission=function(e){for(var s=c.a.getters.element,t=0;t<s.length;t++){if(s[t].toString()==e)return!0}return!1},n.a.config.productionTip=!1,new n.a({el:"#app",store:c.a,router:b,components:{App:r},template:"<App/>"})},PRBW:function(e,s,t){var n={"./views/Dashboard":["npFY",21],"./views/Dashboard.vue":["npFY",21],"./views/account/son/ConfirmForm":["OXv5",0],"./views/account/son/ConfirmForm.vue":["OXv5",0],"./views/account/son/ConfirmPass":["1kbU",18],"./views/account/son/ConfirmPass.vue":["1kbU",18],"./views/account/son/SonAdd":["XXGX",0,60],"./views/account/son/SonAdd.vue":["XXGX",0,60],"./views/account/son/SonEdit":["gq+y",0,59],"./views/account/son/SonEdit.vue":["gq+y",0,59],"./views/account/son/SonList":["GWUL",0,3],"./views/account/son/SonList.vue":["GWUL",0,3],"./views/account/son/StockList":["6l2X",0,58],"./views/account/son/StockList.vue":["6l2X",0,58],"./views/account/son/TableList":["oQmq",0],"./views/account/son/TableList.vue":["oQmq",0],"./views/account/son/Transfer":["22IA",0,57],"./views/account/son/Transfer.vue":["22IA",0,57],"./views/device/devices/DeviceList":["7peB",5],"./views/device/devices/DeviceList.vue":["7peB",5],"./views/device/devices/SonSelect":["0UVG",25],"./views/device/devices/SonSelect.vue":["0UVG",25],"./views/device/devices/StatusSearch":["fkGg",56],"./views/device/devices/StatusSearch.vue":["fkGg",56],"./views/game/games/GameAdd":["M0Gg",0,55],"./views/game/games/GameAdd.vue":["M0Gg",0,55],"./views/game/games/GameEdit":["ibfF",0,54],"./views/game/games/GameEdit.vue":["ibfF",0,54],"./views/game/games/GameList":["/Mh1",0,8],"./views/game/games/GameList.vue":["/Mh1",0,8],"./views/game/prices/GameSearch":["icdD",0],"./views/game/prices/GameSearch.vue":["icdD",0],"./views/game/prices/PriceAdd":["uKtY",0],"./views/game/prices/PriceAdd.vue":["uKtY",0],"./views/game/prices/PriceEdit":["yxO8",0],"./views/game/prices/PriceEdit.vue":["yxO8",0],"./views/game/prices/PriceList":["xyFY",0,23],"./views/game/prices/PriceList.vue":["xyFY",0,23],"./views/game/prices/PricePass":["t3YB",0,20],"./views/game/prices/PricePass.vue":["t3YB",0,20],"./views/layout":["4er+",0,1],"./views/layout.vue":["4er+",0,1],"./views/login":["T+/8",2],"./views/login/":["T+/8",2],"./views/login/index":["T+/8",2],"./views/login/index.vue":["T+/8",2],"./views/menu":["Kn5Q",61],"./views/menu.js":["Kn5Q",61],"./views/public/401":["bsID",22],"./views/public/401.vue":["bsID",22],"./views/public/404":["8s1K",19],"./views/public/404.vue":["8s1K",19],"./views/stock/dists/DistComponent":["kKyb",0,53],"./views/stock/dists/DistComponent.vue":["kKyb",0,53],"./views/stock/dists/DistList":["XQy9",0,6],"./views/stock/dists/DistList.vue":["XQy9",0,6],"./views/stock/dists/EndTime":["AYRZ",52],"./views/stock/dists/EndTime.vue":["AYRZ",52],"./views/stock/dists/FormComponent":["A3Ic",0],"./views/stock/dists/FormComponent.vue":["A3Ic",0],"./views/stock/dists/OnSearch":["nlni",0,51],"./views/stock/dists/OnSearch.vue":["nlni",0,51],"./views/stock/dists/StartTime":["VJTg",50],"./views/stock/dists/StartTime.vue":["VJTg",50],"./views/stock/ins/EndTime":["qvGw",0],"./views/stock/ins/EndTime.vue":["qvGw",0],"./views/stock/ins/InList":["iquw",0,14],"./views/stock/ins/InList.vue":["iquw",0,14],"./views/stock/ins/OnSearch":["0O9q",0,49],"./views/stock/ins/OnSearch.vue":["0O9q",0,49],"./views/stock/ins/StartTime":["l3Cv",0],"./views/stock/ins/StartTime.vue":["l3Cv",0],"./views/stock/migrations/MigrationForm":["lcCM",0],"./views/stock/migrations/MigrationForm.vue":["lcCM",0],"./views/stock/migrations/MigrationList":["l0S8",0,15],"./views/stock/migrations/MigrationList.vue":["l0S8",0,15],"./views/stock/migrations/MigrationModal":["i5ub",0,48],"./views/stock/migrations/MigrationModal.vue":["i5ub",0,48],"./views/stock/outs/EndTime":["lTNL",0],"./views/stock/outs/EndTime.vue":["lTNL",0],"./views/stock/outs/OnSearch":["+NuR",0,47],"./views/stock/outs/OnSearch.vue":["+NuR",0,47],"./views/stock/outs/OutList":["pzkF",0,13],"./views/stock/outs/OutList.vue":["pzkF",0,13],"./views/stock/outs/StartTime":["anpD",0],"./views/stock/outs/StartTime.vue":["anpD",0],"./views/stock/statistics/EndTime":["KlBG",0],"./views/stock/statistics/EndTime.vue":["KlBG",0],"./views/stock/statistics/OnSearch":["rtPm",0,46],"./views/stock/statistics/OnSearch.vue":["rtPm",0,46],"./views/stock/statistics/StartTime":["+xxY",0],"./views/stock/statistics/StartTime.vue":["+xxY",0],"./views/stock/statistics/StatisticList":["GyJ7",0,17],"./views/stock/statistics/StatisticList.vue":["GyJ7",0,17],"./views/stock/stocks/Detail":["khHb",0,45],"./views/stock/stocks/Detail.vue":["khHb",0,45],"./views/stock/stocks/DropDown":["1M1X",0,44],"./views/stock/stocks/DropDown.vue":["1M1X",0,44],"./views/stock/stocks/EndTime":["qRUo",0],"./views/stock/stocks/EndTime.vue":["qRUo",0],"./views/stock/stocks/Onsearch":["KShS",0,43],"./views/stock/stocks/Onsearch.vue":["KShS",0,43],"./views/stock/stocks/StartTime":["T6ww",0],"./views/stock/stocks/StartTime.vue":["T6ww",0],"./views/stock/stocks/StockList":["d5cH",0,4],"./views/stock/stocks/StockList.vue":["d5cH",0,4],"./views/stock/stocks/backup/GameSelect":["kOKt",0,42],"./views/stock/stocks/backup/GameSelect.vue":["kOKt",0,42],"./views/stock/stocks/backup/PriceSelect":["FXYT",0,41],"./views/stock/stocks/backup/PriceSelect.vue":["FXYT",0,41],"./views/stock/stocks/backup/StatusSelect":["2FGY",40],"./views/stock/stocks/backup/StatusSelect.vue":["2FGY",40],"./views/stock/stocks/backup/TypeSelect":["Wtf/",39],"./views/stock/stocks/backup/TypeSelect.vue":["Wtf/",39],"./views/stock/stocks/backup/UserSelect":["eEBT",38],"./views/stock/stocks/backup/UserSelect.vue":["eEBT",38],"./views/stock/stocks/descriptionItem":["ROKw",0],"./views/stock/stocks/descriptionItem.vue":["ROKw",0],"./views/system/configs/ConfAdd":["C2mx",0,37],"./views/system/configs/ConfAdd.vue":["C2mx",0,37],"./views/system/configs/ConfEdt":["bY3c",0,36],"./views/system/configs/ConfEdt.vue":["bY3c",0,36],"./views/system/configs/ConfList":["WmgI",0,9],"./views/system/configs/ConfList.vue":["WmgI",0,9],"./views/system/menus/MenuAdd":["Xnq1",0,35],"./views/system/menus/MenuAdd.vue":["Xnq1",0,35],"./views/system/menus/MenuEdit":["1ySd",0,34],"./views/system/menus/MenuEdit.vue":["1ySd",0,34],"./views/system/menus/MenuList":["FGri",0,7],"./views/system/menus/MenuList.vue":["FGri",0,7],"./views/system/perms/PermAdd":["zL4t",0,33],"./views/system/perms/PermAdd.vue":["zL4t",0,33],"./views/system/perms/PermEdit":["q5TH",0,32],"./views/system/perms/PermEdit.vue":["q5TH",0,32],"./views/system/perms/PermList":["PokA",0,10],"./views/system/perms/PermList.vue":["PokA",0,10],"./views/system/roles/MenuDrawer":["BDJ3",0,31],"./views/system/roles/MenuDrawer.vue":["BDJ3",0,31],"./views/system/roles/PermDrawer":["lzQ5",0,30],"./views/system/roles/PermDrawer.vue":["lzQ5",0,30],"./views/system/roles/RoleAdd":["uATO",29],"./views/system/roles/RoleAdd.vue":["uATO",29],"./views/system/roles/RoleEdit":["noc0",28],"./views/system/roles/RoleEdit.vue":["noc0",28],"./views/system/roles/RoleList":["/uYn",0,12],"./views/system/roles/RoleList.vue":["/uYn",0,12],"./views/system/userinfo/InfoEdit":["0qqb",24],"./views/system/userinfo/InfoEdit.vue":["0qqb",24],"./views/system/userinfo/InfoList":["jT9v",11],"./views/system/userinfo/InfoList.vue":["jT9v",11],"./views/system/users/UserAdd":["Q5/b",27],"./views/system/users/UserAdd.vue":["Q5/b",27],"./views/system/users/UserEdit":["1xmT",26],"./views/system/users/UserEdit.vue":["1xmT",26],"./views/system/users/UserList":["fJvi",16],"./views/system/users/UserList.vue":["fJvi",16]};function i(e){var s=n[e];return s?Promise.all(s.slice(1).map(t.e)).then(function(){return t(s[0])}):Promise.reject(new Error("Cannot find module '"+e+"'."))}i.keys=function(){return Object.keys(n)},i.id="PRBW",e.exports=i},Pa5o:function(e,s,t){"use strict";Object.defineProperty(s,"__esModule",{value:!0});var n=t("X2Oc"),i={path:"/stock",name:"Stock",component:function(){return Promise.all([t.e(0),t.e(1)]).then(t.bind(null,"4er+"))},redirect:"/stock/list",children:[Object(n.a)("/stock/list","/stock/stocks/StockList","StockList","库存列表")]};s.default=i},STSY:function(e,s,t){"use strict";s.a=function(e){return Object(n.a)({url:"/v1/role/store",method:"post",data:e})},s.d=function(e){return Object(n.a)({url:"/v1/role/index",method:"get",params:{id:e}})},s.c=function(e){return Object(n.a)({url:"/v1/role/update",method:"post",data:e})},s.b=function(e){return Object(n.a)({url:"/v1/role/delete",method:"delete",data:e})};var n=t("vLgD")},TVk9:function(e,s,t){"use strict";Object.defineProperty(s,"__esModule",{value:!0});var n=t("X2Oc"),i={path:"/menu",name:"Menu",component:function(){return Promise.all([t.e(0),t.e(1)]).then(t.bind(null,"4er+"))},redirect:"/menu/list",children:[Object(n.a)("/menu/list","/system/menus/MenuList","MenuList","菜单列表"),Object(n.a)("/menu/add","/system/menus/MenuAdd","MenuAdd","菜单添加"),Object(n.a)("/menu/edit","/system/menus/MenuEdit","MenuEdit","菜单修改")]};s.default=i},VGZd:function(e,s,t){"use strict";Object.defineProperty(s,"__esModule",{value:!0});var n=t("X2Oc"),i={path:"/perm",name:"Perm",component:function(){return Promise.all([t.e(0),t.e(1)]).then(t.bind(null,"4er+"))},redirect:"/perm/list",children:[Object(n.a)("/perm/list","/system/perms/PermList","PermList","权限列表"),Object(n.a)("/perm/add","/system/perms/PermAdd","PermAdd","权限添加")]};s.default=i},X2Oc:function(e,s,t){"use strict";s.a=function(e,s,n,i,r){return{exact:!0,path:e,name:n,children:r,meta:{requireAuth:!0,title:i},component:function(){return t("PRBW")("./views"+s)}}}},XN5v:function(e,s){e.exports={_args:[["ant-design-vue@1.4.3","/home/wqzhong/code/vue/vue-games"]],_from:"ant-design-vue@1.4.3",_id:"ant-design-vue@1.4.3",_inBundle:!1,_integrity:"sha1-M1WLoEOB0fRiH4hjcpyM2mT39RQ=",_location:"/ant-design-vue",_phantomChildren:{},_requested:{type:"version",registry:!0,raw:"ant-design-vue@1.4.3",name:"ant-design-vue",escapedName:"ant-design-vue",rawSpec:"1.4.3",saveSpec:null,fetchSpec:"1.4.3"},_requiredBy:["/"],_resolved:"https://registry.npm.taobao.org/ant-design-vue/download/ant-design-vue-1.4.3.tgz",_spec:"1.4.3",_where:"/home/wqzhong/code/vue/vue-games",bugs:{url:"https://github.com/vueComponent/ant-design-vue/issues"},dependencies:{"@ant-design/icons":"^2.1.1","@ant-design/icons-vue":"^2.0.0","add-dom-event-listener":"^1.0.2","array-tree-filter":"^2.1.0","async-validator":"^3.0.3","babel-helper-vue-jsx-merge-props":"^2.0.3","babel-runtime":"6.x",classnames:"^2.2.5","component-classes":"^1.2.6","dom-align":"^1.7.0","dom-closest":"^0.2.0","dom-scroll-into-view":"^1.2.1","enquire.js":"^2.1.6",intersperse:"^1.0.0","is-negative-zero":"^2.0.0",ismobilejs:"^0.5.1",json2mq:"^0.2.0",lodash:"^4.17.5",moment:"^2.21.0","mutationobserver-shim":"^0.3.2","node-emoji":"^1.10.0","omit.js":"^1.0.0",raf:"^3.4.0","resize-observer-polyfill":"^1.5.1","shallow-equal":"^1.0.0",shallowequal:"^1.0.2","vue-ref":"^1.0.4",warning:"^3.0.0"},description:"An enterprise-class UI design language and Vue-based implementation",devDependencies:{"@commitlint/cli":"^6.2.0","@commitlint/config-conventional":"^6.1.3","@octokit/rest":"^15.4.1","@vue/cli-plugin-eslint":"^3.0.5","@vue/server-test-utils":"1.0.0-beta.16","@vue/test-utils":"1.0.0-beta.16",acorn:"^6.0.5",autoprefixer:"^9.6.0",axios:"^0.18.0","babel-cli":"^6.26.0","babel-core":"^6.26.0","babel-eslint":"^10.0.1","babel-helper-vue-jsx-merge-props":"^2.0.3","babel-jest":"^23.6.0","babel-loader":"^7.1.2","babel-plugin-import":"^1.1.1","babel-plugin-inline-import-data-uri":"^1.0.1","babel-plugin-istanbul":"^4.1.1","babel-plugin-syntax-dynamic-import":"^6.18.0","babel-plugin-syntax-jsx":"^6.18.0","babel-plugin-transform-class-properties":"^6.24.1","babel-plugin-transform-decorators":"^6.24.1","babel-plugin-transform-decorators-legacy":"^1.3.4","babel-plugin-transform-es3-member-expression-literals":"^6.22.0","babel-plugin-transform-es3-property-literals":"^6.22.0","babel-plugin-transform-object-assign":"^6.22.0","babel-plugin-transform-object-rest-spread":"^6.26.0","babel-plugin-transform-runtime":"~6.23.0","babel-plugin-transform-vue-jsx":"^3.7.0","babel-polyfill":"^6.26.0","babel-preset-env":"^1.6.1","case-sensitive-paths-webpack-plugin":"^2.1.2",chalk:"^2.3.2",cheerio:"^1.0.0-rc.2",codecov:"^3.0.0",colorful:"^2.1.0",commander:"^2.15.0","compare-versions":"^3.3.0","cross-env":"^5.1.4","css-loader":"^0.28.7","deep-assign":"^2.0.0","enquire-js":"^0.2.1",eslint:"^5.8.0","eslint-config-prettier":"^3.0.1","eslint-plugin-html":"^3.2.2","eslint-plugin-markdown":"^1.0.0","eslint-plugin-vue":"^5.1.0","fetch-jsonp":"^1.1.3","fs-extra":"^7.0.0",glob:"^7.1.2",gulp:"^4.0.1","gulp-babel":"^7.0.0","gulp-strip-code":"^0.1.4","highlight.js":"^9.12.0","html-webpack-plugin":"^3.2.0",husky:"^0.14.3","istanbul-instrumenter-loader":"^3.0.0",jest:"^24.0.0","jest-serializer-vue":"^1.0.0","jest-transform-stub":"^2.0.0","js-base64":"^2.4.8",jsonp:"^0.2.1",less:"^3.9.0","less-loader":"^4.1.0","less-plugin-npm-import":"^2.1.0","lint-staged":"^7.2.2","markdown-it":"^8.4.0","markdown-it-anchor":"^4.0.0",marked:"^0.3.7",merge2:"^1.2.1","mini-css-extract-plugin":"^0.5.0",minimist:"^1.2.0",mkdirp:"^0.5.1",mockdate:"^2.0.2",nprogress:"^0.2.0","optimize-css-assets-webpack-plugin":"^5.0.1",postcss:"^7.0.6","postcss-loader":"^3.0.0","pre-commit":"^1.2.2",prettier:"^1.18.2","pretty-quick":"^1.11.1",querystring:"^0.2.0","raw-loader":"^1.0.0-beta.0",reqwest:"^2.0.5",rimraf:"^2.6.2","rucksack-css":"^1.0.2","selenium-server":"^3.0.1",semver:"^5.3.0","style-loader":"^0.18.2",stylelint:"^9.10.1","stylelint-config-prettier":"^4.0.0","stylelint-config-standard":"^18.2.0",through2:"^2.0.3","uglifyjs-webpack-plugin":"^2.1.1","url-loader":"^1.1.2",vue:"^2.6.10","vue-antd-md-loader":"^1.1.0","vue-clipboard2":"0.0.8","vue-eslint-parser":"^5.0.0","vue-i18n":"^8.3.2","vue-infinite-scroll":"^2.0.2","vue-jest":"^2.5.0","vue-loader":"^15.6.2","vue-router":"^3.0.1","vue-server-renderer":"^2.6.6","vue-template-compiler":"^2.6.10","vue-virtual-scroller":"^0.12.0",vuex:"^3.1.0",webpack:"^4.28.4","webpack-cli":"^3.2.1","webpack-dev-server":"^3.1.14","webpack-merge":"^4.1.1",webpackbar:"^3.1.5"},files:["dist","lib","es","types","scripts"],homepage:"https://vue.ant.design/",husky:{hooks:{"pre-commit":"pretty-quick --staged"}},keywords:["ant","design","antd","vue","vueComponent","component","components","ui","framework","frontend"],license:"MIT",main:"lib/index.js",module:"es/index.js",name:"ant-design-vue",peerDependencies:{vue:">=2.6.6","vue-template-compiler":">=2.6.6"},repository:{type:"git",url:"git+https://github.com/vueComponent/ant-design-vue.git"},scripts:{codecov:"codecov",commitmsg:"commitlint -x @commitlint/config-conventional -e $GIT_PARAMS",compile:"node antd-tools/cli/run.js compile",copy:"node scripts/run.js copy-html",dev:"cross-env NODE_ENV=development ENTRY_INDEX=dev ./node_modules/.bin/webpack-dev-server --open --hot --port 3001",dist:"node antd-tools/cli/run.js dist",lint:"eslint -c ./.eslintrc --fix --ext .jsx,.js,.vue ./components","lint:style":'stylelint "{site,components}/**/*.less" --syntax less',postinstall:'node scripts/postinstall || echo "ignore"',"pre-publish":"node ./scripts/prepub",prepublish:"node antd-tools/cli/run.js guard",prettier:"prettier -c --write '**/*'","pretty-quick":"pretty-quick",pub:"node antd-tools/cli/run.js pub","pub-with-ci":"node antd-tools/cli/run.js pub-with-ci",site:"node scripts/run.js _site",start:"cross-env NODE_ENV=development ./node_modules/.bin/webpack-dev-server --open --hot",test:"cross-env NODE_ENV=test jest --config .jest.js"},sideEffects:["site/*","components/style.js","components/**/style/*","*.vue","*.md","dist/*","es/**/style/*","lib/**/style/*","*.less"],title:"Ant Design Vue",typings:"types/index.d.ts",version:"1.4.3"}},Z79B:function(e,s,t){"use strict";Object.defineProperty(s,"__esModule",{value:!0});var n=t("X2Oc"),i={path:"/son",name:"Son",component:function(){return Promise.all([t.e(0),t.e(1)]).then(t.bind(null,"4er+"))},redirect:"/son/list",children:[Object(n.a)("/son/list","/account/son/SonList","SonList","子账户列表"),Object(n.a)("/son/add","/account/son/SonAdd","SonAdd","子账户添加"),Object(n.a)("/son/edit","/account/son/SonEdit","SonEdit","子账户修改")]};s.default=i},a24j:function(e,s,t){"use strict";Object.defineProperty(s,"__esModule",{value:!0});var n=t("X2Oc"),i={path:"/config",name:"Config",component:function(){return Promise.all([t.e(0),t.e(1)]).then(t.bind(null,"4er+"))},redirect:"/config/list",children:[Object(n.a)("/config/list","/system/configs/ConfList","ConfList","配置列表"),Object(n.a)("/config/add","/system/configs/ConfAdd","ConfAdd","配置添加")]};s.default=i},bREw:function(e,s,t){"use strict";Object.defineProperty(s,"__esModule",{value:!0});var n=t("//Fk"),i=t.n(n),r=t("vMJZ"),o=t("lbHh"),c=t.n(o),a="kc_game",u="expire_in";t("NYxO"),t("IcnI");var l={token:c.a.get(a),expires_in:c.a.get(u),element:[]},d={login:function(e,s){var t=this,n=(e.commit,s.username),o=s.password;return console.log("this is login"),new i.a(function(e,s){Object(r.g)({name:n.trim(),password:o}).then(function(s){var n=s.data;t.dispatch("user/setToken",n.token),e()}).catch(function(e){s(e)})})},setToken:function(e,s){(0,e.commit)("SET_TOKEN",s),function(e){c.a.set(a,e)}(s)},resetToken:function(e){var s=e.commit;return new i.a(function(e){s("SET_TOKEN",""),c.a.remove(a),e()})},refreshToken:function(e){var s=this;e.commit;return new i.a(function(e){Object(r.h)().then(function(e){var t=e.data;s.dispatch("user/setToken",t)}),e()})},getElement:function(e){var s=this;e.commit;return console.log("this is permission"),new i.a(function(e,t){Object(r.e)().then(function(t){var n=t.data;s.dispatch("user/setElement",n),e()}).catch(function(e){t(e)})})},setElement:function(e,s){(0,e.commit)("SET_ELEMENT",s)}};s.default={namespaced:!0,state:l,mutations:{SET_TOKEN:function(e,s){e.token=s},SET_EXPIRE:function(e,s){e.expires_in=s},SET_ELEMENT:function(e,s){e.element=s}},actions:d}},crva:function(e,s,t){"use strict";Object.defineProperty(s,"__esModule",{value:!0});var n=t("X2Oc"),i={path:"/ins",name:"Ins",component:function(){return Promise.all([t.e(0),t.e(1)]).then(t.bind(null,"4er+"))},redirect:"/ins/list",children:[Object(n.a)("/ins/list","/stock/ins/InList","InsList","入库列表")]};s.default=i},gIa0:function(e,s,t){"use strict";Object.defineProperty(s,"__esModule",{value:!0});var n=t("X2Oc"),i={path:"/info",name:"Info",component:function(){return Promise.all([t.e(0),t.e(1)]).then(t.bind(null,"4er+"))},redirect:"/info/list",children:[Object(n.a)("/info/list","/system/userinfo/InfoList","InfoList","详情列表"),Object(n.a)("/info/edit","/system/userinfo/InfoEdit","InfoEdit","用户详情修改")]};s.default=i},gZeQ:function(e,s,t){"use strict";Object.defineProperty(s,"__esModule",{value:!0});var n=t("X2Oc"),i={path:"/out",name:"Out",component:function(){return Promise.all([t.e(0),t.e(1)]).then(t.bind(null,"4er+"))},redirect:"/out/list",children:[Object(n.a)("/out/list","/stock/outs/OutList","OutList","出库列表")]};s.default=i},"hZ/y":function(e,s){},kyiS:function(e,s,t){var n={"./config.js":"a24j","./device.js":"GiYn","./dist.js":"sKeI","./game.js":"KtAA","./ins.js":"crva","./menu.js":"TVk9","./migration.js":"ISR7","./out.js":"gZeQ","./perm.js":"VGZd","./price.js":"ruEz","./role.js":"KsCx","./son.js":"Z79B","./statistic.js":"HBV9","./stock.js":"Pa5o","./user.js":"Bgvw","./userinfo.js":"gIa0"};function i(e){return t(r(e))}function r(e){var s=n[e];if(!(s+1))throw new Error("Cannot find module '"+e+"'.");return s}i.keys=function(){return Object.keys(n)},i.resolve=r,e.exports=i,i.id="kyiS"},qjn9:function(e,s,t){"use strict";Object.defineProperty(s,"__esModule",{value:!0});var n=t("STSY"),i={add:function(e,s){Object(n.a)(s).then(function(e){console.log(e)})}};s.default={namespaced:!0,actions:i}},ruEz:function(e,s,t){"use strict";Object.defineProperty(s,"__esModule",{value:!0});var n=t("X2Oc"),i={path:"/price",name:"Price",component:function(){return Promise.all([t.e(0),t.e(1)]).then(t.bind(null,"4er+"))},redirect:"/price/list",children:[Object(n.a)("/price/list","/game/prices/PriceList","PriceList","面值列表"),Object(n.a)("/price/pass","/game/prices/PricePass","PricePass","面值跳过列表")]};s.default=i},sKeI:function(e,s,t){"use strict";Object.defineProperty(s,"__esModule",{value:!0});var n=t("X2Oc"),i={path:"/dist",name:"Dist",component:function(){return Promise.all([t.e(0),t.e(1)]).then(t.bind(null,"4er+"))},redirect:"/dist/list",children:[Object(n.a)("/dist/list","/stock/dists/DistList","DistList","凭证分配")]};s.default=i},uslO:function(e,s,t){var n={"./af":"3CJN","./af.js":"3CJN","./ar":"3MVc","./ar-dz":"tkWw","./ar-dz.js":"tkWw","./ar-kw":"j8cJ","./ar-kw.js":"j8cJ","./ar-ly":"wPpW","./ar-ly.js":"wPpW","./ar-ma":"dURR","./ar-ma.js":"dURR","./ar-sa":"7OnE","./ar-sa.js":"7OnE","./ar-tn":"BEem","./ar-tn.js":"BEem","./ar.js":"3MVc","./az":"eHwN","./az.js":"eHwN","./be":"3hfc","./be.js":"3hfc","./bg":"lOED","./bg.js":"lOED","./bm":"hng5","./bm.js":"hng5","./bn":"aM0x","./bn.js":"aM0x","./bo":"w2Hs","./bo.js":"w2Hs","./br":"OSsP","./br.js":"OSsP","./bs":"aqvp","./bs.js":"aqvp","./ca":"wIgY","./ca.js":"wIgY","./cs":"ssxj","./cs.js":"ssxj","./cv":"N3vo","./cv.js":"N3vo","./cy":"ZFGz","./cy.js":"ZFGz","./da":"YBA/","./da.js":"YBA/","./de":"DOkx","./de-at":"8v14","./de-at.js":"8v14","./de-ch":"Frex","./de-ch.js":"Frex","./de.js":"DOkx","./dv":"rIuo","./dv.js":"rIuo","./el":"CFqe","./el.js":"CFqe","./en-SG":"oYA3","./en-SG.js":"oYA3","./en-au":"Sjoy","./en-au.js":"Sjoy","./en-ca":"Tqun","./en-ca.js":"Tqun","./en-gb":"hPuz","./en-gb.js":"hPuz","./en-ie":"ALEw","./en-ie.js":"ALEw","./en-il":"QZk1","./en-il.js":"QZk1","./en-nz":"dyB6","./en-nz.js":"dyB6","./eo":"Nd3h","./eo.js":"Nd3h","./es":"LT9G","./es-do":"7MHZ","./es-do.js":"7MHZ","./es-us":"INcR","./es-us.js":"INcR","./es.js":"LT9G","./et":"XlWM","./et.js":"XlWM","./eu":"sqLM","./eu.js":"sqLM","./fa":"2pmY","./fa.js":"2pmY","./fi":"nS2h","./fi.js":"nS2h","./fo":"OVPi","./fo.js":"OVPi","./fr":"tzHd","./fr-ca":"bXQP","./fr-ca.js":"bXQP","./fr-ch":"VK9h","./fr-ch.js":"VK9h","./fr.js":"tzHd","./fy":"g7KF","./fy.js":"g7KF","./ga":"U5Iz","./ga.js":"U5Iz","./gd":"nLOz","./gd.js":"nLOz","./gl":"FuaP","./gl.js":"FuaP","./gom-latn":"+27R","./gom-latn.js":"+27R","./gu":"rtsW","./gu.js":"rtsW","./he":"Nzt2","./he.js":"Nzt2","./hi":"ETHv","./hi.js":"ETHv","./hr":"V4qH","./hr.js":"V4qH","./hu":"xne+","./hu.js":"xne+","./hy-am":"GrS7","./hy-am.js":"GrS7","./id":"yRTJ","./id.js":"yRTJ","./is":"upln","./is.js":"upln","./it":"FKXc","./it-ch":"/E8D","./it-ch.js":"/E8D","./it.js":"FKXc","./ja":"ORgI","./ja.js":"ORgI","./jv":"JwiF","./jv.js":"JwiF","./ka":"RnJI","./ka.js":"RnJI","./kk":"j+vx","./kk.js":"j+vx","./km":"5j66","./km.js":"5j66","./kn":"gEQe","./kn.js":"gEQe","./ko":"eBB/","./ko.js":"eBB/","./ku":"kI9l","./ku.js":"kI9l","./ky":"6cf8","./ky.js":"6cf8","./lb":"z3hR","./lb.js":"z3hR","./lo":"nE8X","./lo.js":"nE8X","./lt":"/6P1","./lt.js":"/6P1","./lv":"jxEH","./lv.js":"jxEH","./me":"svD2","./me.js":"svD2","./mi":"gEU3","./mi.js":"gEU3","./mk":"Ab7C","./mk.js":"Ab7C","./ml":"oo1B","./ml.js":"oo1B","./mn":"CqHt","./mn.js":"CqHt","./mr":"5vPg","./mr.js":"5vPg","./ms":"ooba","./ms-my":"G++c","./ms-my.js":"G++c","./ms.js":"ooba","./mt":"oCzW","./mt.js":"oCzW","./my":"F+2e","./my.js":"F+2e","./nb":"FlzV","./nb.js":"FlzV","./ne":"/mhn","./ne.js":"/mhn","./nl":"3K28","./nl-be":"Bp2f","./nl-be.js":"Bp2f","./nl.js":"3K28","./nn":"C7av","./nn.js":"C7av","./pa-in":"pfs9","./pa-in.js":"pfs9","./pl":"7LV+","./pl.js":"7LV+","./pt":"ZoSI","./pt-br":"AoDM","./pt-br.js":"AoDM","./pt.js":"ZoSI","./ro":"wT5f","./ro.js":"wT5f","./ru":"ulq9","./ru.js":"ulq9","./sd":"fW1y","./sd.js":"fW1y","./se":"5Omq","./se.js":"5Omq","./si":"Lgqo","./si.js":"Lgqo","./sk":"OUMt","./sk.js":"OUMt","./sl":"2s1U","./sl.js":"2s1U","./sq":"V0td","./sq.js":"V0td","./sr":"f4W3","./sr-cyrl":"c1x4","./sr-cyrl.js":"c1x4","./sr.js":"f4W3","./ss":"7Q8x","./ss.js":"7Q8x","./sv":"Fpqq","./sv.js":"Fpqq","./sw":"DSXN","./sw.js":"DSXN","./ta":"+7/x","./ta.js":"+7/x","./te":"Nlnz","./te.js":"Nlnz","./tet":"gUgh","./tet.js":"gUgh","./tg":"5SNd","./tg.js":"5SNd","./th":"XzD+","./th.js":"XzD+","./tl-ph":"3LKG","./tl-ph.js":"3LKG","./tlh":"m7yE","./tlh.js":"m7yE","./tr":"k+5o","./tr.js":"k+5o","./tzl":"iNtv","./tzl.js":"iNtv","./tzm":"FRPF","./tzm-latn":"krPU","./tzm-latn.js":"krPU","./tzm.js":"FRPF","./ug-cn":"To0v","./ug-cn.js":"To0v","./uk":"ntHu","./uk.js":"ntHu","./ur":"uSe8","./ur.js":"uSe8","./uz":"XU1s","./uz-latn":"/bsm","./uz-latn.js":"/bsm","./uz.js":"XU1s","./vi":"0X8Q","./vi.js":"0X8Q","./x-pseudo":"e/KL","./x-pseudo.js":"e/KL","./yo":"YXlc","./yo.js":"YXlc","./zh-cn":"Vz2w","./zh-cn.js":"Vz2w","./zh-hk":"ZUyn","./zh-hk.js":"ZUyn","./zh-tw":"BbgG","./zh-tw.js":"BbgG"};function i(e){return t(r(e))}function r(e){var s=n[e];if(!(s+1))throw new Error("Cannot find module '"+e+"'.");return s}i.keys=function(){return Object.keys(n)},i.resolve=r,e.exports=i,i.id="uslO"},vLgD:function(e,s,t){"use strict";var n=t("//Fk"),i=t.n(n),r=t("mtWM"),o=t.n(r),c=t("2vhu"),a=t("IcnI"),u=o.a.create({baseURL:Object({NODE_ENV:"production"}).VUE_APP_BASE_API,timeout:2e4});u.interceptors.request.use(function(e){return a.a.getters.token&&(e.headers.authorization=a.a.getters.token),e},function(e){return console.log(e),i.a.reject(e)}),u.interceptors.response.use(function(e){var s=e.headers.authorization;s&&(a.a.dispatch("user/resetToken"),a.a.dispatch("user/setToken",s));var t=e.data;return 200!==t.code&&201!==t.code&&204!==t.code?(c.c.error(t.message||"Error"),i.a.reject(new Error(t.message||"Error"))):t},function(e){return console.log(e.response),c.c.error(e.response.data.message?e.response.data.message:e.message),401!==e.response.status&&429!==e.response.status||a.a.dispatch("user/resetToken").then(function(){console.log(e.response),c.a.info({title:"验证失败",content:e.response.data.message?e.response.data.message:e.message}),setTimeout(function(){location.reload()},2e3)}),401===e.response.data.code&&(location.href="/#/401"),i.a.reject(e)}),s.a=u},vMJZ:function(e,s,t){"use strict";s.g=function(e){return Object(n.a)({url:"/v1/auth/login",method:"post",data:e})},s.f=function(e){return Object(n.a)({url:"/v1/user/index",method:"get",params:e})},s.a=function(e){return Object(n.a)({url:"/v1/user/store",method:"post",data:e})},s.d=function(e){return Object(n.a)({url:"/v1/user/update",method:"post",data:e})},s.k=function(e){return Object(n.a)({url:"/v1/user/status",method:"post",data:e})},s.b=function(e){return Object(n.a)({url:"/v1/user/delete",method:"delete",data:e})},s.i=function(e){return Object(n.a)({url:"/v1/user/reset_password",method:"post",data:e})},s.c=function(e){return Object(n.a)({url:"/v1/user/detail",method:"get",params:e})},s.l=function(e){return Object(n.a)({url:"/v1/user/tag_data",method:"get",params:e})},s.h=function(){return Object(n.a)({url:"/v1/auth/refresh",method:"get"})},s.e=function(){return Object(n.a)({url:"/v1/menu/element",method:"get"})},s.j=function(e){return Object(n.a)({url:"/v1/user/select",method:"get",params:e})},s.m=function(e){return Object(n.a)({url:"/v1/user/select",method:"get",params:e})};var n=t("vLgD")},"w+hY":function(e,s,t){var n={"./role.js":"qjn9","./user.js":"bREw"};function i(e){return t(r(e))}function r(e){var s=n[e];if(!(s+1))throw new Error("Cannot find module '"+e+"'.");return s}i.keys=function(){return Object.keys(n)},i.resolve=r,e.exports=i,i.id="w+hY"}},["NHnr"]);
//# sourceMappingURL=app.f3fd980d4e70211db76d.js.map