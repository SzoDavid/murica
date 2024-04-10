<?php

namespace murica_bl\Constants;

class TableDefinition {
    //region User table
    const USER_TABLE = 'MURICA_USER';
    const USER_TABLE_FIELD_ID = 'ID';
    const USER_TABLE_FIELD_NAME = 'NAME';
    const USER_TABLE_FIELD_EMAIL = 'EMAIL';
    const USER_TABLE_FIELD_PASSWORD = 'PASSWORD';
    const USER_TABLE_FIELD_BIRTH_DATE = 'BIRTH_DATE';
    //endregion

    //region Token table
    const TOKEN_TABLE = 'MURICA_TOKENS';
    const TOKEN_TABLE_FIELD_TOKEN = 'TOKEN';
    const TOKEN_TABLE_FIELD_USER_ID = 'USER_ID';
    const TOKEN_TABLE_FIELD_EXPIRES_AT = 'EXPIRES_AT';
    //endregion

    //region Admin table
    const ADMIN_TABLE = 'MURICA_ADMIN';
    const ADMIN_TABLE_FIELD_USER_ID = 'USER_ID';
    //endregion

    //region Message table
    const MESSAGE_TABLE = 'MURICA_MESSAGE';
    const MESSAGE_TABLE_FIELD_USER_ID = 'USER_ID';
    const MESSAGE_TABLE_FIELD_SUBJECT = 'SUBJECT';
    const MESSAGE_TABLE_FIELD_CONTENT = 'CONTENT';
    const MESSAGE_TABLE_FIELD_DATE = 'MESSAGE_DATE';
    //endregion

    //region Programme table
    const PROGRAMME_TABLE = 'MURICA_PROGRAMME';
    const PROGRAMME_TABLE_FIELD_NAME = 'NAME';
    const PROGRAMME_TABLE_FIELD_TYPE = 'TYPE';
    const PROGRAMME_TABLE_FIELD_NO_TERMS = 'NO_TERMS';
    //endregion

    //region Room table
    const ROOM_TABLE = 'MURICA_ROOM';
    const ROOM_TABLE_FIELD_ID = 'ID';
    const ROOM_TABLE_FIELD_CAPACITY = 'CAPACITY';
    //endregion

    //region Student table
    const STUDENT_TABLE = 'MURICA_STUDENT';
    const STUDENT_TABLE_FIELD_USER_ID = 'USER_ID';
    const STUDENT_TABLE_FIELD_PROGRAMME_NAME = 'PROGRAMME_NAME';
    const STUDENT_TABLE_FIELD_PROGRAMME_TYPE = 'PROGRAMME_TYPE';
    const STUDENT_TABLE_FIELD_START_TERM = 'START_TERM';
    //endregion
    
    //region Subject table
    const SUBJECT_TABLE = 'MURICA_SUBJECT';
    const SUBJECT_TABLE_FIELD_ID = 'ID';
    const SUBJECT_TABLE_FIELD_NAME = 'NAME';
    const SUBJECT_TABLE_FIELD_APPROVAL = 'APPROVAL';
    const SUBJECT_TABLE_FIELD_CREDIT = 'CREDIT';
    const SUBJECT_TABLE_FIELD_TYPE = 'TYPE';
    //endregion

    //region Course table
    const COURSE_TABLE = 'MURICA_COURSE';
    const COURSE_TABLE_FIELD_SUBJECT_ID = 'SUBJECT_ID';
    const COURSE_TABLE_FIELD_ID = 'ID';
    const COURSE_TABLE_FIELD_CAPACITY = 'CAPACITY';
    const COURSE_TABLE_FIELD_SCHEDULE = 'SCHEDULE';
    const COURSE_TABLE_FIELD_TERM = 'TERM';
    const COURSE_TABLE_FIELD_ROOM_ID = 'ROOM_ID';
    //endregion

    //region CourseTeach table
    const COURSETEACH_TABLE = 'MURICA_COURSETEACH';
    const COURSETEACH_TABLE_FIELD_USER_ID = 'USER_ID';
    const COURSETEACH_TABLE_FIELD_COURSE_ID = 'COURSE_ID';
    const COURSETEACH_TABLE_FIELD_SUBJECT_ID = 'SUBJECT_ID';
    //endregion
}