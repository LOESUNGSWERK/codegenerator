# Projekt-Verzeichniss
im Unterverzeichniss "data" werden 2 Verzeichnisse erwartet
1) data/Table/*.json
2) data/References/*.json

## data/Table/user.json
```json
{
    "tableName"         : "users", 
    "desctiption"       : "Userverwaltung", 
    "modulName"         : "user",
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