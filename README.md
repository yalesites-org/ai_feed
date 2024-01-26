# AI Feed Module

## Overview

The AI Feed module, developed by Yale ITS, is designed to create a feed of website content that can be ingested by and generate embeddings for an AI search service. The module leverages Drupal to query and prepare content for consumption by a language model integration framework, such as LangChain.

## Features

- Retrieves published nodes accessible to anonymous users.
- Processes content to create a consistent and expected format.
- Generates a JSON feed of website content suitable for ingestion by an AI search service.
- Utilizes Drupal core and entity-related functionalities for content retrieval and processing.

## Feed Shape

This module creates an endpoint of website content at `api/ai/v1/content`. Currently, this feed is limited to published nodes that are accessible to anonymous users. In the future, this may be expanded to include filters and new entity types. The shape of the JSON is as follows:

```json
[
  {
    "url": "yalesites-yale.edu-node-18",
    "source": "drupal"
    "documentType": "node/page",
    "documentId": 18,
    "documentTitle": "Resources and Workshops",
    "documentUrl": "https://yalesites.yale.edu/resource",
    "documentContent": "...",
    "metaTags": "",
    "metaDescription": "",
    "dateCreated": "2023-10-12T16:09:21+00:00",
    "dateModified": "2023-11-30T16:11:18+00:00",
    "dateProcessed": "2024-01-23T16:05:38+00:00",
  },
  { ... }
]
```

| Field           | Type    | Description                                |
|-----------------|---------|--------------------------------------------|
| id              | String  | A unique id used by the search index       |
| documentType    | String  | Follows the pattern entityTypeId/bundle    |
| documentId      | Int     | The entity id                              |
| documentTitle   | String  | The title or label of the entity           |
| documentUrl     | String  | The absolute path to the canonical view    |
| documentContent | String  | The rendered default display of the entity |
| metaTags        | String  | Currently empty but will be used soon      |
| metaDescription | String  | Currently empty but will be used soon      |
| dateCreated     | Date    | Created date as ISO 8601                   |
| dateModified    | Date    | Modified date as ISO 8601                  |
| dateProcessed   | Date    | When this record was generated as ISO 8601 |

## Requirements

- Drupal 8 or later

## Contribution / Collaboration

You are welcome to contribute functionality, bug fixes, or documentation to this module. If you would like to suggest a fix or new functionality you may add a new issue to the GitHub issue queue or you may fork this repository and submit a pull request. For more help please see GitHub's article on fork, branch, and pull requests
