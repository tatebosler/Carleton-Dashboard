# API documentation for sayleshill.xyz

There is only one API endpoint, `sayleshill.xyz/api.php`. The API accepts a dictionary of arrays of strings, which can be thought of as categories and facilities (as presented on sayleshill.xyz). This dictionary must be JSON encoded. If nothing is present, the API will assume `{"Sayles-Hill Campus Center": ["sayles"]}`.

The following are two examples of valid payloads, if they were sent via JavaScript:

```javascript
{"Sayles-Hill Campus Center": ["sayles", "security", "security-office", "sayles-cafe", "bookstore", "post", "onecard", "ccce", "career", "sao", "info", "krlx"],
"Dining": ["burton", "ldc", "east-express", "sayles-cafe", "weitz-cafe", "dominos"],
"Laurence McKinley Gould Library": ["libe", "reference", "libe-it", "writing", "archives"],
"Academic Support": ["msc", "writing", "language"],
"Information Technology Services": ["its", "libe-it", "peps"],
"Recreation": ["rec", "wall-bouldering", "cowling", "cowling-pool", "west", "west-pool", "stadium"],
"Business and Administration": ["registrar", "business", "mail", "print"],
"Other": ["shac", "dacie-moses", "perlman", "idealab"]}
```

```javascript
{"Dining": ["burton", "ldc", "sayles-cafe", "weitz-cafe"]}
```

The keys in the top level array (dictionary) can be any value. You may want to use these to categorize the results. If you do not plan on categorizing results you must still use this model, as shown in the second example.

If the keys of second-level arrays are valid (registered in the database), the API will return a JSON-compliant object for each of them. The most important element of each item is:

- `status`: the current status of the facility. Returns exactly one of `"open"`, `"closed"`, `"warning"`, or `"24h"`. The `warning` status means the facility will close within one hour. (Some facilities may append `-onecard` at the end, this indicates that a restriction status is in effect and you must present your OneCard to enter. This is only true for facility IDs `krlx`, `rec`, `wall-bouldering` and `msc`.)
