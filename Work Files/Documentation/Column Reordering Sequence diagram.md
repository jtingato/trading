```
User                Browser(JS)             RequestHandler           JournalDataManager         SQLite DB             index.php            ViewModel             journalTable.php
 |                       |                         |                        |                        |                     |                      |                        |
 | Drag a column         |                         |                        |                        |                     |                      |                        |
 |──────────────────────>|                         |                        |                        |                     |                      |                        |
 |                       | onEnd(): collect        |                        |                        |                     |                      |                        |
 |                       | new columnOrder         |                        |                        |                     |                      |                        |
 |                       |────────────────────────>|                        |                        |                     |                      |                        |
 |                       |    POST /RequestHandler │                        |                        |                     |                      |                        |
 |                       |                         | decode JSON            |                        |                     |                      |                        |
 |                       |                         |──────────────────────>| updateColumnOrdering() |                     |                      |                        |
 |                       |                         |                        | Write ordering changes |                     |                      |                        |
 |                       |                         |                        |────────────────────────>| UPDATE journal_fields │                     |                        |
 |                       |                         |                        |                        | (ordering saved)    |                      |                        |
 |                       |                         | <──────────────────────| success                |                     |                      |                        |
 |                       | <────────────────────────| HTTP 200               |                        |                     |                      |                        |
 |                       | window.location.reload() │                        |                        |                     |                      |                        |
 |                       |────────────────────────> |                        |                        |                     |                      |                        |
 |                       |                         |                        |                        |  New request        |                      |                        |
 |                       |                         |                        |                        |────────────────────>| include journal.php   |                        |
 |                       |                         |                        |                        |                     | new JournalViewModel()|                        |
 |                       |                         |                        |                        |                     |──────────────────────>| visible field names     |
 |                       |                         |                        |                        |                     |                      | from DB (ORDER BY)      |
 |                       |                         |                        |                        |                     |                      |────────────────────────>|
 |                       |                         |                        |                        |                     |                      |   SELECT field_name     |
 |                       |                         |                        |                        |                     |                      |   FROM journal_fields   |
 |                       |                         |                        |                        |                     |                      |   ORDER BY ordering     |
 |                       |                         |                        |                        |                     |                      | <────────────────────────|
 |                       |                         |                        |                        |                     |                      | load entries (rows)     |
 |                       |                         |                        |                        |                     |                      |────────────────────────>|
 |                       |                         |                        |                        |                     |                      | SELECT * FROM trading_journal
 |                       |                         |                        |                        |                     |                      | <────────────────────────|
 |                       |                         |                        |                        |                     |                      | build header + rows      |
 |                       |                         |                        |                        |                     |                      |────────────────────────>|
 |                       |                         |                        |                        |                     |                      | echo HTML table          |
 |                       |                         |                        |                        |                     | <──────────────────────|                        |
 |                       | <──────────────────────── HTML rendered            |                        |                     |                      |                        |
 | Table realigns        |                         |                        |                        |                     |                      |                        |

```