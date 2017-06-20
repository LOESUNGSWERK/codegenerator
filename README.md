# codegenerator
wandelt ein Datenmodell über freih configurierbare Controler und Views in Files um. So kann mann für jede Sprache code erzeugen..
* das Datenmodell sind json-files mit Datatypes und Master-Detailbeziehungen

## commandline
```
Codecreator vom LOESUNGSWERK
=============================
create options

        --newProject `projektname` :: legt ein neues Projekt unter ./projects/`projektname`/ an und füllt es mit dumydaten 
        --newTemplate `templatetname` :: legt ein neues Template unter ./templares/`templatetname`/ an 

        --generate  :: erzeugt den Quellcode
        --generate --project `projektname` :: erzeugt den quellcode für das angegbene Projet 
        --generate --template`templatetname` :: erzeugt den quellcode für das angegebe Template
        --generate --project `projektname` --template`templatetname` :: erzeugt den quellcode für das angegbene Projet mit dem angegeben Template

        --generateFormDb  :: erzeugt das Datenmodell aus einer Datenbank
        --generateFormDb --localhost --user --pw --datenbank --table --project --overrideIfExists


Beispiele:
create --newProject Demo2.0
create --newTemplate angular.js
create --generateFormDb --localhost localhost --pot 3306 --user test --pw geheim --datenbank testDb --project Demo2.0
create --generate --project Demo2.0 --template angular.js
```


## Projekt-Configuration
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

## Code-Templates
### templates/demo/creator.json
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

## Datenmodell
### projects/Demo/data/Table/user.json
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

## projects/Demo/data/References/content_user.json
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