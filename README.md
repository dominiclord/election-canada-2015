# election-canada-2015
no open data? what year is this

## API

All IDs used are internal and not official Elections Canada identification

| URL                   | HTTP Method   | Operation                            |
| :-------------------- | :------------ | :----------------------------------- |
| `/api/candidates`     | `GET`         | Returns an array of posts            |
| `/api/candidates/:id` | `GET`         | Returns the candidate with id of :id |
| `/api/districts`      | `GET`         | Returns an array of district         |
| `/api/districts/:id`  | `GET`         | Returns the district with id of :id  |
| `/api/parties`        | `GET`         | Returns an array of parties          |
| `/api/parties/:id`    | `GET`         | Returns the party with id of :id     |