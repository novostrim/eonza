<?php

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
*/
