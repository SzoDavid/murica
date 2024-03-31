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
}