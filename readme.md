# VahidIrn\FilterQueryNeo

A trait to provide composable queries to Laravel models.

The intended usage is to provide an easy way to declare how a model's fields are filterQueryNeo and allow the filter query to be expressed in a form that can be easily represented (as a JSON object for example).

The VahidIrn\FilterQueryNeo\FilterQueryNeoTrait adds a filter() method to a Model which works with the Laravel query builder. The filter() method accepts a single argument which is an array of [field => value] pairs that define the search query being made. 

# Install

```
composer require vahidirn/laravel-filter-query-neo4j
```

# Usage

Basic usage is to add the trait to a Model class and configure filterQueryNeo fields.

```
use VahidIrn\FilterQueryNeo\FilterQueryNeo;

class User extends Model
{
  use \VahidIrn\FilterQueryNeo\FilterQueryNeoTrait;
  
  protected $filterQueryNeo = [
    'name' => FilterQueryNeo::String,
    'email' => FilterQueryNeo::String
  ];
}

$filter = [
  'name' => 'John'
];
User::filter($filter)->toSql();
// select * from users where name = ?

$filter = [
  'name_LIKE' => 'Jo%n',
  'email' => 'john@example.com'
];
User::filter($filter)->toSql();
// select * from users where name like ? and email = ?
```

The `$filterQueryNeo` property defines the fields that may be used in filter queries. The value is an array of *filter rules* that lists the possible variations the filter may use.

The built-in rules are:

* EQ - equality
* Regex - regular expression pattern matching
* MATCH - wildcard pattern matching
* CONTAINS - substr
* MIN - greater than or equal to
* MAX - less than or equal to
* GT - greater than
* LT - less than
* RE - regular expression
* FT - full text search (see notes below)
* IN - contained in list
* NULL - null comparison

A standard set of rules are provided

* String = [EQ, LIKE, ILIKE, MATCH]
* Numeric = [EQ, MIN, MAX, LT, GT]
* Enum = [EQ, IN]
* Date = [EQ, MIN, MAX, LT, GT]
* Boolean = [EQ]

A model's filterQueryNeo definition sets the rules available for each field. The first rule in the list is the default rule for the field. Other rules must add the rule name as a suffix in the filter query field name.

In the following definition, the 'name' field can use any of the String rules: EQ, LIKE, ILIKE, MATCH.

```
User::$filterQueryNeo = [
  'name' => FilterQueryNeo::String
]
```

A query can thus use:

```
$filter = [
  'name_MATCH' => 'John'
];
User::filter($filter);
```

The SQL that is run will match any user whose name is a case-insensitive match containing 'John', such as 'Little john', 'Johnathon', 'JOHN'.

The model's filterQueryNeo definition can also be return dynamically by overloading the getFilterQueryNeo() method on the class.

```
class User extends Model
{
  use \VahidIrn\FilterQueryNeo\FilterQueryNeoTrait;
  
  public function getFilterQueryNeo () {
    return [
      'name' => FilterQueryNeo::String,
      'email' => FilterQueryNeo::String
    ];
  }
}
```

# Custom rules

A class may provide custom rules to apply to fields.

```
class User extends Model
{
  use \VahidIrn\FilterQueryNeo\FilterQueryNeoTrait;
  protected $filterQueryNeo = [
    'keyword' => 'Keyword'
  ];
  public function scopeFilterKeyword($query, $field, $arg) {
    $query->where(function ($q) use ($arg) {
      $q->where('name', $arg);
      $q->orWhere('email', $arg);
      $q->orWhere('phone', $arg);
    });
    return $query;
  }
}
```

Custom rules can be listed in the $filterQueryNeo definition along with the built-in rules and work in the same way. The first rule in $filterQueryNeo is the default rule for the field. If it's not the first rule it must have the rule name added as a suffix to the field name in the query.

Rule names are converted to 'ucfirst' and appended to 'scopeFilter'.

# Negating rules

Rules may be negated by using the 'NOT' modifier for default rules, or prefixing the rule modifier with 'NOT_' for other rules.

```
// anyone but John
$filter = [
  'name_NOT' => 'John'
];
User::filter($filter)->toSql();
// select * from users where name != ?

// any status except 'active' or 'expired'
$filter = [
  'status_NOT_IN' => ['active', 'expired']
];
User::filter($filter)->toSql();
// select * from users where status not in (?, ?)
```

Note: the comparison rules (MIN, MAX, LT, GT) do not have negated forms.

Custom rules may implemented a negated version by defining a corresponding scope function that implements the functionality. The function is named similarly, but with the word 'Not' before the rule name.

```
class User extends Model
{
   ...
   public function scopeFilterNotKeyword($query, $field, $arg) {
     return $query
       ->where('name', '!=', $arg)
       ->where('email', '!=', $arg)
       ->where('phone', '!=', $arg);
   }
}
```

# Logical combinations with AND, OR, NOT, and NOR

A filter can create more complex queries by structuring the filter into nested queries.

```
$filter = [
  'OR' => [
    [
      'name' => 'John'
    ],
    [
      'name' => 'Alice'
    ]
  ]
];
User::filter($filter)->toSql();
// select * from users where ((name = ?) or (name = ?))
```

The 'AND', 'OR', 'NOT', and 'NOR' nesting operators each take a list of nested filters to apply. Nested filter queries can in turn use the nesting operators to create more complex queries.

# Relationships

Relationships can be used to filter related models.

```

class Post extends Model
{
  use \VahidIrn\FilterQueryNeo\FilterQueryNeoTrait;
  
  public function getFilterQueryNeo () {
    return [
      'comment' => $this->comments()
    ];
  }
  
  public function comments() {
    return $this->hasMany(Comment::class);
  }
}

class Comment extends Model
{
  use \VahidIrn\FilterQueryNeo\FilterQueryNeoTrait;
  
  public function getFilterQueryNeo () {
    return [
      'created_at' => FilterQueryNeo::Date
    ];
  }
  
  public function post() {
    return $this->belongsTo(Post::class);
  }
}

# Get all posts that have a comment in June 2017
$filter = [
  'comment' => [
    'created_at_MIN' => '2017-06-01',
    'cerated_at_MAX' => '2017-07-01'
  ]
];
Post::filter($filter)->toSql()
// select * from "posts"
// inner join (
//   select distinct "comments"."post_id" from "comments"
//   where "created_at" >= ? and "created_at" <= ?
// ) as "comments_1" on "posts"."id" = "comments_1"."post_id"
```

# Chaining filters

Filters are deeply merged when chaining.

```
class Post extends Model
{
  use \VahidIrn\FilterQueryNeo\FilterQueryNeoTrait;
  
  public function getFilterQueryNeo () {
    return [
      'comment' => $this->comments()
    ];
  }
  
  public function comments() {
    return $this->hasMany(Comment::class);
  }
}

class User extends Model
{
  use \VahidIrn\FilterQueryNeo\FilterQueryNeoTrait;
  
  protected $filterQueryNeo = [
    'id' => FilterQueryNeo::Integer
  ];
}

class Comment extends Model
{
  use \VahidIrn\FilterQueryNeo\FilterQueryNeoTrait;
  
  public function getFilterQueryNeo () {
    return [
      'created_at' => FilterQueryNeo::Date,
      'author' => $this->author()
    ];
  }
  
  public function post() {
    return $this->belongsTo(Post::class);
  }
  
  public function author() {
    return $this->belongsTo(User::class);
  }
}
$filter1 = [
  'comment' => [
    'created_at_MIN' => '2017-06-01',
    'created_at_MAX' => '2017-07-01'
  ]
];
$filter2 = [
  'comment' => [
    'author' => [
      'id' => 1
    ]
  ]
];

Post::filter($filter1)->filter($filter2)->toSql()
// select * from "posts"
// inner join (
//   select distinct "comments"."post_id"
//   from "comments"
//   inner join (
//     select distinct "users"."id" as "author_id" from "users"
//     where "id" = ?
//   ) as "authors_1" on "comments"."author_id" = "authors_1"."author_id"
//   where "created_at" >= ? and "created_at" <= ?
// ) as "comments_1" on "posts"."id" = "comments_1"."post_id"
```

To not do a deep merge the `filterApply()` method can be used.

```
Post::filter($filter1)->filterApply()->filter($filter2)->toSql()
// select * from "posts"
// inner join (
//   select distinct "comments"."post_id" from "comments"
//   where "created_at" >= ? and "created_at" <= ?
// ) as "comments_1" on "posts"."id" = "comments_1"."post_id"
// inner join (
//   select distinct "comments"."post_id" from "comments"
//   inner join (
//     select distinct "users"."id" as "author_id" from "users"
//     where "id" = ?
//   ) as "authors_1" on "comments"."author_id" = "authors_1"."author_id"
// ) as "comments_2" on "posts"."id" = "comments_2"."post_id"
```

# Full text search

The `FilterQueryNeo::FT` rule provides a basic form of full text search in PostgreSQL using tsearch.

To make use of the full text rule the application must provide a table populated with search data. By default, the table is named according to the model name with `_filterQueryNeo` as suffix, and has a one-to-one mapping using the same primary key as the model's table. The tsearch vector data is stored in a column using the field's name with `_vector` suffix. The table and vector field names can be customised by provide values for the filterQueryNeoFtTable and filterQueryNeoFtVector properties.

```
-- Content model table
create table posts (id int primary key, body text);
-- Full text search data
create table posts_filterQueryNeo (id int references posts (id), body_vector tsvector);
```

The application must ensure the vector field is appropriately updated (usually by defining trigger functions).

```
class Post
{
  use \VahidIrn\FilterQueryNeo\FilterQueryNeoTrait;
  protected $filterQueryNeo = [
    'body' => FilterQueryNeo::FT
  ];
}
$filter = [
  'body' => 'fat cats'
];
Post::filter($filter)->orderBy('body_rank', 'desc')->toSql();
// select * from "posts" inner join (
//  select "id", ts_rank("body_vector", query) as "body_rank"
//  from "posts_filterQueryNeo"
//  cross join plainto_tsquery(?) query
//  where "body_vector" @@ "query"
// ) as body_1 on "posts"."id" = "body_1"."id"
// order by "body_rank" desc
```

The model may provide custom values for the search table, foreign key, and field names.

```
class Post
{
  use \VahidIrn\FilterQueryNeo\FilterQueryNeoTrait;
  protected $filterQueryNeo = [
    'body' => FilterQueryNeo::FT
  ];
  protected $filterQueryNeoFtTable = 'search';
  protected $filterQueryNeoFtKeyName = 'post_id';
  protected $filterQueryNeoFtVector = 'data';
}
```

These custom values may also be evaluated dynamically by providing "get" functions.

```
class Post
{
  use \VahidIrn\FilterQueryNeo\FilterQueryNeoTrait;
  protected $filterQueryNeo = [
    'body' => FilterQueryNeo::FT
  ];
  public function getFilterQueryNeoFtTable ($field) {
    return 'search';
  };
  public function getFilterQueryNeoFtKeyName ($field) {
    return 'post_id';
  }
  public function getFilterQueryNeoFtVector ($field) {
    return $field;
  }
}
```
