<?php

/* Header
    enz\0 - str
    version - ushort
    size - ushort
    time - YYYYMMDDHHMMSS str
*/

define( 'ENZ_VERSION', 1 );
define( 'ENZ_HEADSIZE', 22 );

/* Commands
    cmd - byte  
    size - uint 
*/
define( 'CMD_TABLE', 1 );
/*
    id - uint
    uptime - uint
    istree - byte
    title - str
    alias - str
    comment - str
    columns count - ushort 
        for each column
            size - ushort
            id - uint
            idtype - byte
            sort - ushort
            visible - byte
            align - byte
            title alias comment extend - str
    indexes count - ubyte
        for each index
            fields - str   INDEX ( ... ) or FULLINDEX ( ... )
*/
