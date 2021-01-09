<?php
namespace App\Helpers;
use \Exception;

final class lpExceptionMsgHandler {
    private const ARR_POSTGRES_ERRORS = array(
        '00000' => 'SUCCESSFUL COMPLETION',
        '01000' => 'WARNING',
        '0100C' => 'DYNAMIC RESULT SETS RETURNED',
        '01008' => 'IMPLICIT ZERO BIT PADDING',
        '01003' => 'NULL VALUE ELIMINATED IN SET FUNCTION',
        '01007' => 'PRIVILEGE NOT GRANTED',
        '01006' => 'PRIVILEGE NOT REVOKED',
        '01004' => 'STRING DATA RIGHT TRUNCATION',
        '01P01' => 'DEPRECATED FEATURE',
        '02000' => 'NO DATA',
        '02001' => 'NO ADDITIONAL DYNAMIC RESULT SETS RETURNED',
        '03000' => 'SQL STATEMENT NOT YET COMPLETE',
        '08000' => 'CONNECTION EXCEPTION',
        '08003' => 'CONNECTION DOES NOT EXIST',
        '08006' => 'CONNECTION FAILURE',
        '08001' => 'SQLCLIENT UNABLE TO ESTABLISH SQLCONNECTION',
        '08004' => 'SQLSERVER REJECTED ESTABLISHMENT OF SQLCONNECTION',
        '08007' => 'TRANSACTION RESOLUTION UNKNOWN',
        '08P01' => 'PROTOCOL VIOLATION',
        '09000' => 'TRIGGERED ACTION EXCEPTION',
        '0A000' => 'FEATURE NOT SUPPORTED',
        '0B000' => 'INVALID TRANSACTION INITIATION',
        '0F000' => 'LOCATOR EXCEPTION',
        '0F001' => 'INVALID LOCATOR SPECIFICATION',
        '0L000' => 'INVALID GRANTOR',
        '0LP01' => 'INVALID GRANT OPERATION',
        '0P000' => 'INVALID ROLE SPECIFICATION',
        '21000' => 'CARDINALITY VIOLATION',
        '22000' => 'DATA EXCEPTION',
        '2202E' => 'ARRAY SUBSCRIPT ERROR',
        '22021' => 'CHARACTER NOT IN REPERTOIRE',
        '22008' => 'DATETIME FIELD OVERFLOW',
        '22012' => 'DIVISION BY ZERO',
        '22005' => 'ERROR IN ASSIGNMENT',
        '2200B' => 'ESCAPE CHARACTER CONFLICT',
        '22022' => 'INDICATOR OVERFLOW',
        '22015' => 'INTERVAL FIELD OVERFLOW',
        '2201E' => 'INVALID ARGUMENT FOR LOGARITHM',
        '2201F' => 'INVALID ARGUMENT FOR POWER FUNCTION',
        '2201G' => 'INVALID ARGUMENT FOR WIDTH BUCKET FUNCTION',
        '22018' => 'INVALID CHARACTER VALUE FOR CAST',
        '22007' => 'INVALID DATETIME FORMAT',
        '22019' => 'INVALID ESCAPE CHARACTER',
        '2200D' => 'INVALID ESCAPE OCTET',
        '22025' => 'INVALID ESCAPE SEQUENCE',
        '22P06' => 'NONSTANDARD USE OF ESCAPE CHARACTER',
        '22010' => 'INVALID INDICATOR PARAMETER VALUE',
        '22020' => 'INVALID LIMIT VALUE',
        '22023' => 'INVALID PARAMETER VALUE',
        '2201B' => 'INVALID REGULAR EXPRESSION',
        '22009' => 'INVALID TIME ZONE DISPLACEMENT VALUE',
        '2200C' => 'INVALID USE OF ESCAPE CHARACTER',
        '2200G' => 'MOST SPECIFIC TYPE MISMATCH',
        '22004' => 'NULL VALUE NOT ALLOWED',
        '22002' => 'NULL VALUE NO INDICATOR PARAMETER',
        '22003' => 'NUMERIC VALUE OUT OF RANGE',
        '22026' => 'STRING DATA LENGTH MISMATCH',
        '22001' => 'STRING DATA RIGHT TRUNCATION',
        '22011' => 'SUBSTRING ERROR',
        '22027' => 'TRIM ERROR',
        '22024' => 'UNTERMINATED C STRING',
        '2200F' => 'ZERO LENGTH CHARACTER STRING',
        '22P01' => 'FLOATING POINT EXCEPTION',
        '22P02' => 'INVALID TEXT REPRESENTATION',
        '22P03' => 'INVALID BINARY REPRESENTATION',
        '22P04' => 'BAD COPY FILE FORMAT',
        '22P05' => 'UNTRANSLATABLE CHARACTER',
        '23000' => 'INTEGRITY CONSTRAINT VIOLATION',
        '23001' => 'RESTRICT VIOLATION',
        '23502' => 'NOT NULL VIOLATION',
        '23503' => 'FOREIGN KEY VIOLATION',
        '23505' => 'UNIQUE VIOLATION',
        '23514' => 'CHECK VIOLATION',
        '24000' => 'INVALID CURSOR STATE',
        '25000' => 'INVALID TRANSACTION STATE',
        '25001' => 'ACTIVE SQL TRANSACTION',
        '25002' => 'BRANCH TRANSACTION ALREADY ACTIVE',
        '25008' => 'HELD CURSOR REQUIRES SAME ISOLATION LEVEL',
        '25003' => 'INAPPROPRIATE ACCESS MODE FOR BRANCH TRANSACTION',
        '25004' => 'INAPPROPRIATE ISOLATION LEVEL FOR BRANCH TRANSACTION',
        '25005' => 'NO ACTIVE SQL TRANSACTION FOR BRANCH TRANSACTION',
        '25006' => 'READ ONLY SQL TRANSACTION',
        '25007' => 'SCHEMA AND DATA STATEMENT MIXING NOT SUPPORTED',
        '25P01' => 'NO ACTIVE SQL TRANSACTION',
        '25P02' => 'IN FAILED SQL TRANSACTION',
        '26000' => 'INVALID SQL STATEMENT NAME',
        '27000' => 'TRIGGERED DATA CHANGE VIOLATION',
        '28000' => 'INVALID AUTHORIZATION SPECIFICATION',
        '2B000' => 'DEPENDENT PRIVILEGE DESCRIPTORS STILL EXIST',
        '2BP01' => 'DEPENDENT OBJECTS STILL EXIST',
        '2D000' => 'INVALID TRANSACTION TERMINATION',
        '2F000' => 'SQL ROUTINE EXCEPTION',
        '2F005' => 'FUNCTION EXECUTED NO RETURN STATEMENT',
        '2F002' => 'MODIFYING SQL DATA NOT PERMITTED',
        '2F003' => 'PROHIBITED SQL STATEMENT ATTEMPTED',
        '2F004' => 'READING SQL DATA NOT PERMITTED',
        '34000' => 'INVALID CURSOR NAME',
        '38000' => 'EXTERNAL ROUTINE EXCEPTION',
        '38001' => 'CONTAINING SQL NOT PERMITTED',
        '38002' => 'MODIFYING SQL DATA NOT PERMITTED',
        '38003' => 'PROHIBITED SQL STATEMENT ATTEMPTED',
        '38004' => 'READING SQL DATA NOT PERMITTED',
        '39000' => 'EXTERNAL ROUTINE INVOCATION EXCEPTION',
        '39001' => 'INVALID SQLSTATE RETURNED',
        '39004' => 'NULL VALUE NOT ALLOWED',
        '39P01' => 'TRIGGER PROTOCOL VIOLATED',
        '39P02' => 'SRF PROTOCOL VIOLATED',
        '3B000' => 'SAVEPOINT EXCEPTION',
        '3B001' => 'INVALID SAVEPOINT SPECIFICATION',
        '3D000' => 'INVALID CATALOG NAME',
        '3F000' => 'INVALID SCHEMA NAME',
        '40000' => 'TRANSACTION ROLLBACK',
        '40002' => 'TRANSACTION INTEGRITY CONSTRAINT VIOLATION',
        '40001' => 'SERIALIZATION FAILURE',
        '40003' => 'STATEMENT COMPLETION UNKNOWN',
        '40P01' => 'DEADLOCK DETECTED',
        '42000' => 'SYNTAX ERROR OR ACCESS RULE VIOLATION',
        '42601' => 'SYNTAX ERROR',
        '42501' => 'INSUFFICIENT PRIVILEGE',
        '42846' => 'CANNOT COERCE',
        '42803' => 'GROUPING ERROR',
        '42830' => 'INVALID FOREIGN KEY',
        '42602' => 'INVALID NAME',
        '42622' => 'NAME TOO LONG',
        '42939' => 'RESERVED NAME',
        '42804' => 'DATATYPE MISMATCH',
        '42P18' => 'INDETERMINATE DATATYPE',
        '42809' => 'WRONG OBJECT TYPE',
        '42703' => 'UNDEFINED COLUMN',
        '42883' => 'UNDEFINED FUNCTION',
        '42P01' => 'UNDEFINED TABLE',
        '42P02' => 'UNDEFINED PARAMETER',
        '42704' => 'UNDEFINED OBJECT',
        '42701' => 'DUPLICATE COLUMN',
        '42P03' => 'DUPLICATE CURSOR',
        '42P04' => 'DUPLICATE DATABASE',
        '42723' => 'DUPLICATE FUNCTION',
        '42P05' => 'DUPLICATE PREPARED STATEMENT',
        '42P06' => 'DUPLICATE SCHEMA',
        '42P07' => 'DUPLICATE TABLE',
        '42712' => 'DUPLICATE ALIAS',
        '42710' => 'DUPLICATE OBJECT',
        '42702' => 'AMBIGUOUS COLUMN',
        '42725' => 'AMBIGUOUS FUNCTION',
        '42P08' => 'AMBIGUOUS PARAMETER',
        '42P09' => 'AMBIGUOUS ALIAS',
        '42P10' => 'INVALID COLUMN REFERENCE',
        '42611' => 'INVALID COLUMN DEFINITION',
        '42P11' => 'INVALID CURSOR DEFINITION',
        '42P12' => 'INVALID DATABASE DEFINITION',
        '42P13' => 'INVALID FUNCTION DEFINITION',
        '42P14' => 'INVALID PREPARED STATEMENT DEFINITION',
        '42P15' => 'INVALID SCHEMA DEFINITION',
        '42P16' => 'INVALID TABLE DEFINITION',
        '42P17' => 'INVALID OBJECT DEFINITION',
        '44000' => 'WITH CHECK OPTION VIOLATION',
        '53000' => 'INSUFFICIENT RESOURCES',
        '53100' => 'DISK FULL',
        '53200' => 'OUT OF MEMORY',
        '53300' => 'TOO MANY CONNECTIONS',
        '54000' => 'PROGRAM LIMIT EXCEEDED',
        '54001' => 'STATEMENT TOO COMPLEX',
        '54011' => 'TOO MANY COLUMNS',
        '54023' => 'TOO MANY ARGUMENTS',
        '55000' => 'OBJECT NOT IN PREREQUISITE STATE',
        '55006' => 'OBJECT IN USE',
        '55P02' => 'CANT CHANGE RUNTIME PARAM',
        '55P03' => 'LOCK NOT AVAILABLE',
        '57000' => 'OPERATOR INTERVENTION',
        '57014' => 'QUERY CANCELED',
        '57P01' => 'ADMIN SHUTDOWN',
        '57P02' => 'CRASH SHUTDOWN',
        '57P03' => 'CANNOT CONNECT NOW',
        '58030' => 'IO ERROR',
        '58P01' => 'UNDEFINED FILE',
        '58P02' => 'DUPLICATE FILE',
        'F0000' => 'CONFIG FILE ERROR',
        'F0001' => 'LOCK FILE EXISTS',
        'P0000' => 'PLPGSQL ERROR',
        'P0001' => 'RAISE EXCEPTION',
        'XX000' => 'INTERNAL ERROR',
        'XX001' => 'DATA CORRUPTED',
        'XX002' => 'INDEX CORRUPTED'
    );

    static function getMessage(Exception $e) {
        $excpName        = get_class($e);
        $errorCode       = $e->getCode();
        $isDatabaseError = (strpos($excpName, 'Database') !== false);
        $arrPostgresErr  = lpExceptionMsgHandler::ARR_POSTGRES_ERRORS;

        if( $isDatabaseError && array_key_exists($errorCode, $arrPostgresErr) ){
            return $arrPostgresErr[$errorCode];
        }
        else {
            return $e->getMessage();
        }
    }
}