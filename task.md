### Adding bodies to Article

The current form of article only has a title, we would like you to add the first
of many elements to the articles. 

We have prepared some of the migrations and Models. The Article may
have many types of elements attached to it. In order to achieve this, a polymorphic
relation should be used. Within the table `article_elements` you can see how those
elements should be linked to the article.

You may add additional relations and models as you see fit.

### Endpoints

Creating multiple endpoints to manage bodies is not required. You may use the existing 
`/api/articles` endpoint to transfer information for article elements. Just make sure
to split up the code in the controller and use services or repositories as you see fit.

The `GET /api/articles` and `GET /api/articles/{article}` endpoint should not generate any
database queries to the `articles` table, make use of our caching. You will need to create
a list of article ids for which you could create a CacheModel or simply use some other
cache that is refreshed when a new article is created or deleted.

### Database

In order to not couple our database to the structure of our application we would like
to not use the model namespaces within the polymorphic relation. Find a way to do this.

### Datetime in responses

Dates like `created_at` and `updated_at` returned by the API should always be 
formatted as `DateTime::ATOM`. Please find a way of formatting them
without the developer actively calling `->toAtomString()` or `->format(DateTime::ATOM)`
in the resources all the time. 

### Caching

Within `app/Cache/Models` you will find that we already created the new 
ArticleElementCache Model for you. Make sure to use it when caching the article
elements. The key for the `ArticleElementCache` should be the article id. This
will allow fetching the elements with `ArticleCache::with('elements')->find($articleId)`
as the relation is already defined within the `ArticleCache` Model

Make sure to always keep the `ArticleElementCache` up to date by using events and listeners
when changes occur.

### Tests

The included tests provide coverage for the current articles endpoint. You will need
to update these tests and add new tests to verify your implementation.

### Frontend
Create a VueJS app with a CSS framework of your choice. Make use of animations.

Connect to the API and show a table of all articles, 
the table only shows the `title` and `updated_at` time in a human friendly format. 
The user can click on an article to view it with all the elements. Create a page where 
the user can edit the article with its title and elements as well as adding new elements.
Each paragraph should be saved as it's own body element. No HTML input should be allowed
within the body elements.
 
The Vue app may or may not be in a docker container. That is up to you and not part of
the task.