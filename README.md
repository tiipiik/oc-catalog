## Catalog plugin for OctoberCMS
Manage and display product categories, product lists and product details, with custom fields

### Install
Use Tiipiik.Catalog to search/install this plugin.

### Features
* Multiple categories
* Multiple levels of categories
* Manage categories order
* Create custom fields for products

### Manage categories
Define categories and manage orders, there's no limit.

### Custom fields for products
Custom fields are created and managed globally and edited specifically for each product. You define the custom field and add a default value if you want. After that, you can change the value of each custom field in each product.

## Components usage

### Categories
Display a list of categories, with different parameters:

* Choose the template to render the category list
* Display subcategories
* Make custom title from the component parameters
* Choose render view : you can actually use `menu_list` or `ìmage_list` to display categories by different ways. You can also create your render view if needed.

### Product list
Display a list of products related to the current category.

### Product details
Display informations about a product, including custom fields.

Display the featured image of a product :
	
	<img src="{{ product.featured_images[0].thumb(300, 300) }}"
        title="{{ product.featured_images[0].title }}"
        alt="{{ product.featured_images[0].description }}">

Display all the featured images :
    
	{% for image in product.featured_images %}
        <img src="{{ image.thumb(300, 300) }}"
             title="{{ image.title }}"
             alt="{{ image.description }}">
    {% endfor %}

Display custom fields of a product :

	{% for field in product.customfields %}
		{{ field.name }} : {{ field.value }} // Will return a list (ie. Color : Green, Size : S, M, L, XL)
	{% endfor %}

Sibling a specific custom field :

	{{ product.customfields[0].name }} // Will return custom field name (ie. "Color")
	and
	{{ product.customfields[0].value }} // Will return custom field value (ie. "Green")

Where 0 is the position of the custom field in list. So use 0 to target the first custom field, 1 for the second, etc.

### TODO
* Enable manual category filter for product list.
* Add categories and products to the Sitemap plugin if installed.

### Plugin under development
This plugin can be used as well, but is still in development, so take care of what you do with.

### Why is this plugin a paid plugin ?
This plugin requires a lot of time to develop. The actual price would be used for support and future development.

### Like this plugin?
If you like this plugin give it a Like. :)

### License
The Catalog plugin for OctoberCMS is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).