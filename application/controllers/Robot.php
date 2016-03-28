<?php

class Robot extends MY_Controller
{
    function __construct(){
        parent::__construct();
        
    }
    
    public function feed(){
        $pages = array();
        $last_update = 0;
        
        $this->load->model('Gallery_model', 'Gallery');
        $this->load->model('Post_model', 'Post');
        $this->load->library('ObjectFormatter', '', 'formatter');
        
        // GALLERIES
        $galleries = $this->Gallery->getByCond([], 25);
        if($galleries){
            $galleries = $this->formatter->gallery($galleries, false, false);
            foreach($galleries as $gallery){
                $pages[] = (object)array(
                    'page' => base_url($gallery->page),
                    'description' => $gallery->seo_description->value ? $gallery->seo_description : $gallery->description->chars(160),
                    'title' => $gallery->name,
                    'categories' => []
                );
                if($gallery->created->time > $last_update)
                    $last_update = $gallery->created->time;
            }
        }
        
        // POSTS
        $cond = array(
            'status'    => 4
        );
        $posts = $this->Post->getByCond($cond, 100);
        if($posts){
            $posts = $this->formatter->post($posts, false, ['category']);
            foreach($posts as $post){
                $page = (object)array(
                    'page' => base_url($post->page),
                    'description' => $post->seo_description->value ? $post->seo_description : $post->content->chars(160),
                    'title' => $post->title,
                    'categories' => []
                );
                
                if(property_exists($post, 'category')){
                    foreach($post->category as $cat)
                        $page->categories[] = $cat->name;
                }
                
                $pages[] = $page;
                
                if($post->published->time > $last_update)
                    $last_update = $post->published->time;
            }
        }
        
        $this->output->set_header('Content-Type: application/xml');
        $this->output->set_header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $last_update) . ' GMT');
        
        $params = array('pages' => $pages);
        
        $params['feed_url'] = base_url('/feed.xml');
        $params['feed_title'] = $this->setting->item('site_name');
        $params['feed_owner_url'] = base_url();
        $params['feed_description'] = $this->setting->item('site_frontpage_description');
        $params['feed_image_url'] = $this->theme->asset('/static/image/logo/feed.jpg');
        
        $this->load->view('robot/feed', $params);
    }
    
    public function sitemap(){
        $pages = array();
        $last_update = 0;
        
        $two_days_ago = new DateTime();
        $two_days_ago->sub(new DateInterval('P2D'));
        $two_days_ago = $two_days_ago->format('Y-m-d');
        
        $this->load->model('Gallery_model', 'Gallery');
        $this->load->model('Post_model', 'Post');
        $this->load->model('Postcategory_model', 'PCategory');
        $this->load->model('Postcategorychain_model', 'PCChain');
        $this->load->model('Posttag_model', 'PTag');
        $this->load->model('Posttagchain_model', 'PTChain');
        $this->load->model('User_model', 'User');
        $this->load->model('Page_model', 'Page');
        $this->load->library('ObjectFormatter', '', 'formatter');
        
        // GALLERIES
        $cond = array(
            'created' => (object)['>', $two_days_ago]
        );
        $galleries = $this->Gallery->getByCond($cond, true);
        if($galleries){
            $galleries = $this->formatter->gallery($galleries, false, false);
            foreach($galleries as $gallery){
                $pages[] = (object)array(
                    'loc' => base_url($gallery->page),
                    'lastmod' => $gallery->created->format('Y-m-d'),
                    'changefreq' => 'yearly',
                    'priority' => '0.5',
                    'image_loc' => $gallery->cover,
                    'image_caption' => $gallery->name
                );
                if($gallery->created->time > $last_update)
                    $last_update = $gallery->created->time;
            }
        }
        
        // USERS
        $cond = array(
            'created' => (object)['>', $two_days_ago]
        );
        $users = $this->User->getByCond($cond, true);
        if($users){
            $users = $this->formatter->user($userss, false, false);
            foreach($users as $user){
                $pages[] = (object)array(
                    'loc' => base_url($user->page),
                    'lastmod' => $user->created->format('Y-m-d'),
                    'changefreq' => 'yearly',
                    'priority' => '0.5',
                    'image_loc' => $user->avatar,
                    'image_caption' => $user->fullname
                );
                
                if($user->created->time > $last_update)
                    $last_update = $user->created->time;
            }
        }
        
        // PAGES
        $cond = array(
            'created' => (object)['>', $two_days_ago]
        );
        $static_pages = $this->Page->getByCond($cond, true);
        if($static_pages){
            $static_pages = $this->formatter->page($static_pages, false, false);
            foreach($static_pages as $page){
                $pages[] = (object)array(
                    'loc' => base_url($page->page),
                    'lastmod' => $page->created->format('Y-m-d'),
                    'changefreq' => 'yearly',
                    'priority' => '0.5'
                );
                
                if($page->created->time > $last_update)
                    $last_update = $page->created->time;
            }
        }
        
        // POSTS
        $cond = array(
            'published' => (object)['>', $two_days_ago],
            'status'    => 4
        );
        $posts = $this->Post->getByCond($cond, true);
        if($posts){
            $posts = $this->formatter->post($posts, false, false);
            foreach($posts as $post){
                $pages[] = (object)array(
                    'loc' => base_url($post->page),
                    'lastmod' => $post->published->format('Y-m-d'),
                    'changefreq' => 'yearly',
                    'priority' => '0.5',
                    'image_loc' => $post->cover,
                    'image_caption' => $post->title
                );
                
                if($post->published->time > $last_update)
                    $last_update = $post->published->time;
            }
        }
        
        // POST CATEGORIES
        $cond = array(
            'created' => (object)['>', $two_days_ago]
        );
        $post_categories_chain = $this->PCChain->countGroupedByCond($cond, 'post_category');
        if($post_categories_chain){
            $post_categories_id = array_keys($post_categories_chain);
            $post_categories = $this->PCategory->get($post_categories_id, true);
            
            $post_categories = $this->formatter->post_category($post_categories, false, false);
            foreach($post_categories as $category){
                $pages[] = (object)array(
                    'loc' => base_url($category->page),
                    'lastmod' => $category->created->format('Y-m-d'), // TODO POST CATEGORY UDPATED STATUS
                    'changefreq' => 'weekly',
                    'priority' => '0.7'
                );
                
                if($category->created->time > $last_update)
                    $last_update = $category->created->time;
            }
        }
        
        // POST TAGS
        $cond = array(
            'created' => (object)['>', $two_days_ago]
        );
        $post_tags_chain = $this->PTChain->countGroupedByCond($cond, 'post_tag');
        if($post_tags_chain){
            $post_tags_id = array_keys($post_tags_chain);
            $post_tags = $this->PTag->get($post_tags_id, true);
            
            $post_tags = $this->formatter->post_tag($post_tags, false, false);
            foreach($post_tags as $tag){
                $pages[] = (object)array(
                    'loc' => base_url($tag->page),
                    'lastmod' => $tag->created->format('Y-m-d'), // TODO POST TAG UDPATED STATUS
                    'changefreq' => 'weekly',
                    'priority' => '0.7'
                );
                
                if($tag->created->time > $last_update)
                    $last_update = $tag->created->time;
            }
        }
        
        // FRONT PAGE
        $front_page = (object)array(
            'loc' => base_url(),
            'lastmod' => date('Y-m-d', $last_update),
            'changefreq' => 'hourly',
            'priority' => '0.8',
            'image_loc' => $this->theme->asset('/static/image/logo/logo.png'),
            'image_caption' => $this->setting->item('site_name')
        );
        array_unshift($pages, $front_page);
        
        $this->output->set_header('Content-Type: application/xml');
        $this->output->set_header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $last_update) . ' GMT');
        
        $params = array('pages' => $pages);
        $this->load->view('robot/sitemap', $params);
    }
    
    public function sitemapNews(){
        if(!$this->setting->item('sitemap_news'))
            return $this->show_404();
        
        $pages = array();
        $last_update = 0;
        
        $this->load->model('Post_model', 'Post');
        $this->load->library('ObjectFormatter', '', 'formatter');
        
        $cond = array(
            'status' => 4,
            'seo_schema' => 'NewsArticle'
        );
        
        // get the news posts only
        $posts = $this->Post->getByCond($cond, 100);
        if(!$posts)
            return $this->show_404();
        
        $posts = $this->formatter->post($posts, false, false);
        
        $pages = array();
        $publisher = $this->setting->item('site_name');
        
        foreach($posts as $post){
            $page = (object)array(
                'page'      => base_url($post->page),
                'publisher' => $publisher,
                'published' => $post->published->format('c'),
                'title'     => $post->seo_title ? $post->seo_title : $post->title,
                'keywords'  => $post->seo_keywords
            );
            $pages[] = $page;
            if($post->published->time > $last_update)
                $last_update = $post->published->time;
        }
        
        $this->output->set_header('Content-Type: application/xml');
        $this->output->set_header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $last_update) . ' GMT');
        
        $params = array('pages' => $pages);
        $this->load->view('robot/sitemap-news', $params);
    }
}