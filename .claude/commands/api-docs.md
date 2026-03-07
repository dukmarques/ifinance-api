Load the Prosperify API contract from GitHub for one or more domains. Domain name(s) should be provided as arguments (e.g. `expenses`, `revenues expenses`).

## Steps

1. Fetch the globals file:
   `https://raw.githubusercontent.com/dukmarques/ifinance-api/master/docs/api/_globals.md`

2. For each domain in $ARGUMENTS, fetch:
   `https://raw.githubusercontent.com/dukmarques/ifinance-api/master/docs/api/{domain}.md`

3. Confirm to the user which files were loaded and summarize the available endpoints.

## Available domains
`auth` · `users` · `categories` · `cards` · `expense-assignees` · `expenses` · `revenues` · `card-expenses`

## After loading
Wait for the user's task. Use the fetched contract as the sole source of truth for endpoints, request fields, response shapes, and enum values. Do not invent fields or behaviors not present in the docs.
