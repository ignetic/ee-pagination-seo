Pagination Rel Links
=======================

For ExpressionEngine 2

This extension enables you to add next and prev rel links to paginated pages.
This creates HTML link elements with rel="next" and rel="prev" to indicate the relationship between component URLs in a paginated series.
See: http://googlewebmastercentral.blogspot.co.uk/2011/09/pagination-with-relnext-and-relprev.html

To activate this extension, go to Addons > Extensions and click Install.
Once installed you can change these settings:

- Enable redirect to first page where pagination is not found
- Page number prefix: add a prefix to page numbers in titles
- Page number suffix: add a suffix to page numbers in titles
- Display first page number in titles? i.e. Omit Page #1 in {pagination_seo:page_num} tag


Simply place the following tags within the HTML head area to output the full link tag:
--------------------------------------------------------------------------------------

{pagination_seo:prev}
{pagination_seo:next}

An example output would be:

`<link rel="prev" href="http://www.example.com/articles/P10" />`
`<link rel="next" href="http://www.example.com/articles/P30" />`

Note: These tags can also be used in NSM Better Meta settings.


You may also use the following tags to only output the URL and URI:
-------------------------------------------------------------------

Outputs full link tags: <link rel="next" href="http://www.example.com/articles/P30" />
{pagination_seo:prev}
{pagination_seo:next}

Outputs pagination URI - Example: articles/P30
{pagination_seo:prev_uri}
{pagination_seo:next_uri}

Outputs pagination URL: http://www.example.com/articles/P30
{pagination_seo:prev_url}
{pagination_seo:next_url}

Displays the page number for use in browser titles and elsewhere:
{pagination_seo:page_num}


These tags also work within NSM Better Meta fields


Other tags for unique content:
------------------------------

Configured page numbering to use within the title tag
{pagination_seo:title}

Configured page numbering to use within the meta description tag
{pagination_seo:description}



Extension Settings
------------------

- Titles: add page numbering to paginated page titles. 
  This can be configured with these variables: Available tags: {page_num} {total_pages} {total_items}
  Example configuration: " - Page {page_num}"
  Example use: `<title>{title}{pagination_seo:title}</title>`
  Example output: `<title>My News Articles - Page 2</title>`
  
- Description: add page numbering to paginated page meta description. 
  This can be configured with these variables: Available tags: {page_num} {total_pages} {total_items}
  Example configuration: "Page {page_num} of {total_pages} ({total_items} items) for "
  Example use: `<meta name="description" content="{pagination_seo:description}{meta_description}" />`
  Example output: `<meta name="description" content="Page 2 of 4 for My Articles" />`

- Display on first page?
  Enable this to show the Page Numbering on the first paginated page (default=No)

- Enable redirect to first page where pagination is not found?
  This will help avoid out-of-range pagination problems which automatically redirects to the first page.
  i.e. where the url has P100 but pagination only goes up to P50

- Store pagination strings to allow for cached pages (experimental)
  The problem exists where pagination rules cannot be accessed after they have been cached (with e.g. CE Cache)
  This attempts to circumvent this problem while still being able to display the pagination_seo tags


**This extension uses the following hooks:**

pagination_create
template_post_parse


Installation: copy files to system > expressionengine > third_party
