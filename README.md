# codegenerator
wandelt ein Datenmodell über freih configurierbare Controler und Views in Files um. So kann mann für jede Sprache code erzeugen..
* das Datenmodell sind json-files mit Datatypes und Master-Detailbeziehungen

```json
{
  "projects"       : [
      {
          "caption"     : "Demo Projekt",
          "project"     : "Demo", /* path in "./projects" */
          "template"    : "demo" /* path to codetemplate in "./tempaltes/" */
      }
  ]
}
```