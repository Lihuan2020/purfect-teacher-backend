window.axios = require('axios');

const token = document.head.querySelector('meta[name="csrf-token"]');
const API_TOKEN = document.head.querySelector('meta[name="api-token"]');

if (token) {
    window.axios.defaults.headers.common = {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': token.content,
        'Authorization': 'Bearer ' + API_TOKEN.content,
        'Accept': 'application/json',
    };
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}

import { Constants } from '../common/constants';
import { Util } from '../common/utils';
import { queryStudentProfile, loadMajorDetail } from '../common/registration_form';

window.Vue = require('vue');
// 引入 Element UI 库
import ElementUI from 'element-ui';
import 'element-ui/lib/theme-chalk/index.css';
Vue.use(ElementUI);

Vue.component('major-cards', require('../components/statics/MajorCards.vue').default);
Vue.component('major-registration-form', require('../components/statics/MajorRegistrationForm.vue').default);
Vue.component('file-manager', require('../components/fileManager/FileManager.vue').default);

require('../includes/frontend/student_registration_app');// 学生报名页面程序

// 学生启动申请流程
require('../includes/pipeline/flow_open_app');