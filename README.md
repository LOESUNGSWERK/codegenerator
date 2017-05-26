#codegenerator
wandelt ein Datenmodell über freih configurierbare Controler und Views in Files um. So kann mann für jede Sprache code erzeugen..
* das Datenmodell sind json-files mit Datatypes und Master-Detailbeziehungen


##Projekt-Configuration
```json
{
  "projects"       : [
      {
          "caption"     : "Demo Projekt",
          "project"     : "Demo",
          "template"    : "demo" 
      }
  ]
}
```

##Code-Template
```json
{
  "creator": "LÖSUNGSWERK",
  "user": "Rene Kühle",
  "caption": "Demo Applikation",
  "description": "zeigt die einfache Funktion des ganzen",
  "target": "./simpleDemo/",
  "tasks": [
     {
      "caption": "Index Seite",
      "insertTemplateFile": "index/index.html",
      "destinationFile": "index.html",
      "onUpdate": "REPLACE",
      "replaceTasks": [
        {
          "detect" : "<!-- detect {{tableName|ucf}} li -->",
          "replaceAfter" : "<!-- myDatenmodelleLi -->",
          "templateFile" : "index/datenmodelleLi.html"
        },
        {
          "detect" : "<!-- detect {{tableName|ucf}} Div -->",
          "replaceAfter" : "<!-- myDatenmodelleDiv -->",
          "templateFile" : "index/datenmodellDiv.html"
        }
      ],
      "aktiv": true
    },
    {
      "caption": "Datemmodel",
      "insertTemplateFile": "dm.html",
      "destinationFile": "{{tableName|ucf}}.html",
      "onUpdate": "OVERWRIDE",
      "aktiv": true
    }
  ]
}
```

##Datenmodell
### data/Table/user.json
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

## data/References/content_user.json
```json
[
  {
    "masterTable": "users",
    "masterField": "id",
    "childrenTable": "content",
    "childrenField": "user_id",
    "onDelete": null,
    "onUpdate": null,
    "onInsert": null
  }
]
```