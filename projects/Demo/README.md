# Projekt-Verzeichniss
im Unterverzeichniss "data" werden 2 Verzeichnisse erwartet
1) data/Table/*.json
2) data/References/*.json

## data/Table/user.json
```json
{
    "tableName"         : "users", /* Name der tabelle in der Db */
    "desctiption"       : "Userverwaltung", /* Beschreibung */ 
    "modulName"         : "user", /* Name des Modules zu dem die Tabelle gehört */
    "isDepricated"      : false, 
    "tableType"         : "table",
    "fields":[ 
      {
          "fieldName"     : "id",
          "fieldType"     : "integer",
          "defaultValue"  : null,
          "isAutoinc"     : true,
          "isPrimaryKey"  : true,
          "isIndex"       : false,
          "canBeNull"     : false
      }
    ]
}
```