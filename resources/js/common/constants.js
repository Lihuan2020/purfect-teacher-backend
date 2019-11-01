/**
 * 可能需要广泛使用的一些常量
 */
export const Constants = {
    AJAX_SUCCESS: 1000,
    AJAX_ERROR: 999,
    API: {
        LOAD_TIME_SLOTS_BY_SCHOOL: '/api/school/load-time-slots',
        LOAD_STUDY_TIME_SLOTS_BY_SCHOOL: '/api/school/load-study-time-slots',
        LOAD_BUILDINGS_BY_SCHOOL: '/api/school/load-buildings',
        LOAD_ROOMS_BY_BUILDING: '/api/school/load-building-rooms',
        LOAD_AVAILABLE_ROOMS_BY_BUILDING: '/api/school/load-building-available-rooms',
        LOAD_MAJORS_BY_SCHOOL: '/api/school/load-majors',
        LOAD_GRADES_BY_MAJOR: '/api/school/load-major-grades',
        LOAD_COURSES_BY_MAJOR: '/api/school/load-major-courses',
        LOAD_COURSES_BY_SCHOOL: '/api/school/load-courses',
        LOAD_TEACHERS_BY_COURSE: '/api/school/load-course-teachers',
        SEARCH_TEACHERS_BY_NAME: '/api/school/search-teachers',
        QUICK_SEARCH_USERS_BY_NAME: '/api/school/quick-search-users',
        SAVE_COURSE: '/api/school/save-course',
        DELETE_COURSE: '/api/school/delete-course',
        // 课程表专有
        TIMETABLE: {
            CAN_BE_INSERTED: '/api/timetable/timetable-item-can-be-inserted',
            SAVE_NEW: '/api/timetable/save-timetable-item',
            UPDATE: '/api/timetable/update-timetable-item',
            DELETE_ITEM: '/api/timetable/delete-timetable-item',
            PUBLISH_ITEM: '/api/timetable/publish-timetable-item',
            CLONE_ITEM: '/api/timetable/clone-timetable-item',
            LOAD_TIMETABLE: '/api/timetable/load', // 加载课程表
            LOAD_TIMETABLE_ITEM: '/api/timetable/load-item', // 加载课程表项
            CREATE_SPECIAL_CASE: '/api/timetable/create-special-case', // 加载课程表项
            LOAD_SPECIAL_CASES: '/api/timetable/load-special-cases', // 加载课程表项
            SWITCH_WEEK_VIEW: '/api/timetable/switch-week-view', // 加载课程表项
            // 从不同角度观察课程表的 url
            VIEW_TIMETABLE_FOR_COURSE: '/school_manager/timetable/manager/view-course-timetable', // 从课程的角度加载课程表
            VIEW_TIMETABLE_FOR_TEACHER: '/school_manager/timetable/manager/view-teacher-timetable', // 从课程的授课老师角度加载课程表
            VIEW_TIMETABLE_FOR_ROOM: '/school_manager/timetable/manager/view-room-timetable', // 从课程的授课老师角度加载课程表
        },
        // 申请
        ENQUIRY_SUBMIT: '/api/enquiry/save',
        // 静态页面
    },
    YEARS: ['N.A','一年级','二年级','三年级','四年级','五年级','六年级'],
    TERMS: ['N.A','第一学期','第二学期'],
    REPEAT_UNITS: ['每周重复','仅单周重复','仅双周重复'],
    WEEK_DAYS: ['周一','周二','周三','周四','周五','周六','周日',],
    ENQUIRY_TYPES: ['请假','外出, 出差','报销','用章','用车','场地','物品领用','其他'],
    WEEK_NUMBER_ODD: 1, // 单周
    WEEK_NUMBER_EVEN: 2,// 双周
    LOGIC: {
        TIMETABLE: {
            ENQUIRY: 'timetable-enquiry'
        }
    }
};