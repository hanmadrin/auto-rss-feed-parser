<?php
add_action('init', 'my_custom_post_recipe');
function my_custom_post_recipe()
{
    $labels = array(
        'name'               => _x('Recipes', 'post type general name'),
        'singular_name'      => _x('Recipe', 'post type singular name'),
        'add_new'            => _x('New Recipe', 'book'),
        'add_new_item'       => __('New Recipe'),
        'edit_item'          => __('Edit Recipe'),
        'new_item'           => __('New Recipe'),
        'all_items'          => __('All Recipes'),
        'view_item'          => __('View Recipe'),
        'search_items'       => __('Search Recipes'),
        'not_found'          => __('No Recipes found'),
        'not_found_in_trash' => __('No Recipes found in the Trash'),
        'parent_item_colon'  => ’,
        'menu_name'          => 'Recipes'
    );
    $args = array(
        'labels'                  => $labels,
        'description'             => 'Recipes from our product',
        'public'                  => true,
        'menu_icon'               => 'dashicons-vault',
        'menu_position'           => 25,
        'supports'                => array('title', 'thumbnail', 'decription'),
        'has_archive'             => true,
        'hierarchical'            => false,
        'register_meta_box_cb'    => 'register_recipe_meta_boxes',
        'rewrite' => array('slug' => 'recepten'),
        'show_ui' => true,
    );
    register_post_type('recipe', $args);
}




function custom_columns($columns)
{
    $columns = array(
        'cb' => '<input type="checkbox" />',
        'featured_image' => 'Image',
        'title' => 'Title',
        'recipe_info' => 'Recipe Information',
        'recipe_categories' => 'Categories',
        'recipe_filters' => 'Filters',
        'recipe_products' => 'Products'


    );
    return $columns;
}
add_filter('manage_recipe_posts_columns', 'custom_columns');

function custom_columns_data($column, $post_id)
{
    switch ($column) {
        case 'featured_image':
            the_post_thumbnail('shop_thumbnail');
            break;
        case 'recipe_info':
            $data[0]['meta_name'] = 'Prepare Time:';
            $data[0]['meta_value'] = (get_post_meta($post_id, 'recipe_info_timep' , true)=='')?'N/A':get_post_meta($post_id, 'recipe_info_timep' , true).'min';
            $data[1]['meta_name'] = 'Waiting Time:';
            $data[1]['meta_value'] = (get_post_meta($post_id, 'recipe_info_timew' , true)=='')?'N/A':get_post_meta($post_id, 'recipe_info_timew' , true).'min';
            $data[2]['meta_name'] = 'Serve for:';
            $data[2]['meta_value'] = (get_post_meta($post_id, 'recipe_info_person' , true)=='')?'N/A':get_post_meta($post_id, 'recipe_info_person' , true).'person';
            $data[3]['meta_name'] = 'Recipe Difficulty:';
            $data[3]['meta_value'] = (get_post_meta($post->ID, 'recipe_info_difficulty', true))?'N?A':get_post_meta($post_id,'recipe_info_difficulty',true);
            $data[4]['meta_name'] = 'Recipe Course:';
            $data[4]['meta_value'] = (get_post_meta($post->ID, 'recipe_info_course', true))?'N/A':get_post_meta($post_id,'recipe_info_course',true);

            $content = '
            <style>
            table.recipe_info_holder{
                border:0px;
            }
            table.recipe_info_holder,table.recipe_info_holder tr,table.recipe_info_holder td,table.recipe_info_holder th{
                border:1px solid #f7f7f7;
                border-spacing: 0;
                padding:0px;
            }
            table.recipe_info_holder th{
                padding-right:20px;
            }
            </style>
            <table class="recipe_info_holder">
                '.recipe_info_loop_content($data).'
            </table>
            ';
            echo $content;
            break;
        case 'recipe_categories':
            $arr = array();
            $terms = get_the_terms( $post_id, 'recipe_category' );
            foreach($terms as $term)
            {
                $arr[] = $term->name; 
            }
            $terms = implode(',',$arr);
            echo $terms;
            break;
        case 'recipe_filters':
            $arr = array();
            $terms = get_the_terms( $post_id, 'recipe_tag' );
            foreach($terms as $term)
            {
                $arr[] = $term->name; 
            }
            $terms = implode(',',$arr);
            echo $terms;
            break;
        case 'recipe_products':
            $products = explode(',',get_post_meta($post_id, $column , true));
            foreach($products as $product_id)
            {
                echo '<div style="white-space: nowrap;overflow: hidden;text-overflow: ellipsis;">'.get_the_title($product_id).'</div>';
            }
            break;

    }
}
add_action('manage_recipe_posts_custom_column', 'custom_columns_data', 10, 2);
function recipe_info_loop_content($datas)
{
    $content = '';
    foreach($datas as $data)
    {
        $content .='
        <tr>
            <th>'.$data['meta_name'].'</th>
            <td>'.$data['meta_value'].'</td>
        </tr>
        ';
    }
    return $content;
}



function register_recipe_meta_boxes()
{
    add_meta_box('recipe_info', 'Recipe Information', 'recipe_info_display', 'recipe');
    add_meta_box('recipe_steps', 'Recipe Steps', 'recipe_steps_display', 'recipe');
    add_meta_box('recipe_nutritions', 'Recipe Nutritions', 'recipe_nutritions_display', 'recipe');
    add_meta_box('recipe_products', 'Recipe Products', 'recipe_products_display', 'recipe');
}
add_action('add_meta_boxes', 'register_recipe_meta_boxes');



function recipe_info_display($post)
{
    wp_nonce_field(-1, 'recipe_nonce');
    $content = '
    <table style="width:100%">
        <tr>
            <td><label for="recipe_info_timep">Prepare:</label></td>
            <td><input id="recipe_info_timep" value="' . get_post_meta($post->ID, 'recipe_info_timep', true) . '" type="text"  name="recipe_info_timep" placeholder="i.e. 30 min" /></td>
        </tr>
        <tr>
            <td><label for="recipe_info_timew">Wait:</label></td>
            <td><input id="recipe_info_timew" value="' . get_post_meta($post->ID, 'recipe_info_timew', true) . '" type="text"  name="recipe_info_timew" placeholder="i.e. 30 min" /></td>
        </tr>
        <tr>
            <td><label for="recipe_info_person">For:</label></td>
            <td><input id="recipe_info_person" value="' . get_post_meta($post->ID, 'recipe_info_person', true) . '" type="text"  name="recipe_info_person" placeholder="i.e 4" /></td>
        </tr>
        <tr>
            <td><label for="recipe_info_difficulty">Difficulty:</label></td>
            <td><input id="recipe_info_difficulty" value="' . get_post_meta($post->ID, 'recipe_info_difficulty', true) . '" type="text"  name="recipe_info_difficulty" placeholder="i.e Hard/easy/medium" /></td>
        </tr>
        <tr>
            <td><label for="recipe_info_course">Course:</label></td>
            <td><input id="recipe_info_course" value="' . get_post_meta($post->ID, 'recipe_info_course', true) . '" type="text"  name="recipe_info_course" placeholder="i.e Lunch/Dinner" /></td>
        </tr>
    </table>
    
    ';
    echo $content;
}
function recipe_steps_display($post)
{
    $content = '
    <textarea name="recipe_steps" style="width:100%; height:300px;">' . get_post_meta($post->ID, 'recipe_steps', true) . '</textarea>
    ';
    echo $content;
}
function recipe_nutritions_display($post)
{
    $content = '
    <textarea name="recipe_nutritions" style="width:100%; height:300px;">' . get_post_meta($post->ID, 'recipe_nutritions', true) . '</textarea>
    ';
    echo $content;
}
function recipe_products_display($post)
{
    $content = '
    <style>
    .hover_show_effect{
        opacity:0;
    }
    .hover_show_effect:hover{
        opacity:1;
    }
    .full_view_button{
        top:0px;
        left:0px;
        position:absolute;
        width:100%;
        height:100%;
        cursor:pointer;
    }
    .select_search_recipe_product{
        
    }
    .recipe_single_holder{
        position:relative;
        margin-top:5px;
        margin-bottom:5px;
        border:1px solid #666666;
        border-radius:5px;
        padding:5px;
    }
    .recipe_single_bg_holder{
        padding-left:50px;
        background-size:40px;
        background-position-x:5px;
        background-position-y:5px;
        background-repeat:no-repeat;

    }
    </style>
    <div style="display:flex;">
        <input type="text" id="recipe_product_search"></input>
        <button id="recipe_product_search_button" type="button">Search</button>
    </div>
    <div id="recipe_product_search_show">

    </div>
    <div style="color:black;font-weight:bold;">Recipe Product List:</div>
    <div id="added_recipe_product_list">
    ' . saved_recipe_products(get_post_meta($post->ID, 'recipe_products', true)) . '
    </div>
    <input hidden id="recipe_products_input_ids" type="text" name="recipe_products" value="' . get_post_meta($post->ID, 'recipe_products', true) . '">
    <script>
        
    </script>

    ';
    echo $content;
}
function saved_recipe_products($ids)
{
    $content = '';
    $ids = explode(',', $ids);
    //return count($ids);
    foreach ($ids as $id) {
        if (!empty($id)) {
            $product = wc_get_product($id);
            $data['title'] = get_the_title($id);
            $data['price'] = $product->get_price();
            $data['image'] = wp_get_attachment_image_src(get_post_thumbnail_id($id), 'single-post-thumbnail')[0];
            $content .= '
            <div class="recipe_single_holder">
                <div class="recipe_single_bg_holder"
                style="background-image:url(' . $data['image'] . ');
                ">
                    <div>
                        <span>' . $data['title'] . '</span>
                    </div>
                    <div>&euro;' . $data['price'] . '</div>
                </div>
                <button type="button" class="remove_search_recipe_product full_view_button hover_show_effect" data-id="' . $id . '">
                    Remove this from recipe
                </button>
            </div>
            ';
        }
    }
    return $content;
}
add_action('save_post', 'recipe_info_save');
function recipe_info_save($post_id)
{
    update_post_meta($post_id, 'recipe_info_time', 'lihuh');
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return;

    if (!wp_verify_nonce($_POST['recipe_nonce']))
        return;

    if ('recipe' == $_POST['post_type']) {
        if (!current_user_can('edit_page', $post_id))
            return;
    } else {
        if (!current_user_can('edit_post', $post_id))
            return;
    }
    $recipe_info_timep = $_POST['recipe_info_timep'];
    $recipe_info_timew = $_POST['recipe_info_timew'];
    $recipe_info_person = $_POST['recipe_info_person'];
    $recipe_info_difficulty = $_POST['recipe_info_difficulty'];
    $recipe_info_course = $_POST['recipe_info_course'];
    $recipe_steps = $_POST['recipe_steps'];
    $recipe_nutritions = $_POST['recipe_nutritions'];
    $recipe_products = $_POST['recipe_products'];

    update_post_meta($post_id, 'recipe_info_timep', $recipe_info_timep);
    update_post_meta($post_id, 'recipe_info_timew', $recipe_info_timew);
    update_post_meta($post_id, 'recipe_info_person', $recipe_info_person);
    update_post_meta($post_id, 'recipe_info_difficulty', $recipe_info_difficulty);
    update_post_meta($post_id, 'recipe_info_course', $recipe_info_course);
    update_post_meta($post_id, 'recipe_steps', $recipe_steps);
    update_post_meta($post_id, 'recipe_nutritions', $recipe_nutritions);
    update_post_meta($post_id, 'recipe_products', $recipe_products);
}





add_action('init', 'my_taxonomies_recipe', 0);
function my_taxonomies_recipe()
{
    $labels = array(
        'name'              => _x('Recipe Categories', 'taxonomy general name'),
        'singular_name'     => _x('Recipe Category', 'taxonomy singular name'),
        'search_items'      => __('Search Recipe Categories'),
        'all_items'         => __('All Recipe Categories'),
        'parent_item'       => __('Parent Recipe Category'),
        'parent_item_colon' => __('Parent Recipe Category:'),
        'edit_item'         => __('Edit Recipe Category'),
        'update_item'       => __('Update Recipe Category'),
        'add_new_item'      => __('Add New Recipe Category'),
        'new_item_name'     => __('New Recipe Category'),
        'menu_name'         => __('Recipe Categories'),
    );
    $args = array(
        'labels' => $labels,
        'hierarchical' => true,
        //'rewrite' => array( 'slug' => 'recepten_categorie'),
    );
    register_taxonomy('recipe_category', 'recipe', $args);
}
add_action('init', 'my_tags_recipe', 0);
function my_tags_recipe()
{
    $labels = array(
        'name'              => _x('Recipe Filters', 'taxonomy general name'),
        'singular_name'     => _x('Recipe Filter', 'taxonomy singular name'),
        'search_items'      => __('Search Recipe Filters'),
        'all_items'         => __('All Recipe Filters'),
        'parent_item'       => __('Parent Recipe Filter'),
        'parent_item_colon' => __('Parent Recipe Filter:'),
        'edit_item'         => __('Edit Recipe Filter'),
        'update_item'       => __('Update Recipe Filter'),
        'add_new_item'      => __('Add New Recipe Filter'),
        'new_item_name'     => __('New Recipe Filter'),
        'menu_name'         => __('Recipe Filter'),
    );
    $args = array(
        'labels' => $labels,
        'hierarchical' => true,
        //'rewrite' => array( 'slug' => 'recepten_tag'),
    );
    register_taxonomy('recipe_tag', 'recipe', $args);
}
