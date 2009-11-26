<?php
/**
 * Facebook style paginator
 *
 * @param page the page number (1-based)
 * @param total_items the total amount of items
 * @param limit the amount of items to show per page
 * @param ajax an array with each of clickrewrite[url|id|form|loadimg|hide]
 * @param url the url the links point to
 * @param query_string the string to be appended to url
 * @param item name the name of items shown in summary
 * @param position whether paginator is on top or bottom of page
 * @return pagination string to be placed in html code
 */
 
 /*
  * Todd Chaffee:
  * Fixed: "Prev" link, was set to $firstpage instead of $prev.
  * Fixed: Where there were not items was showing "Displaying 1 to 0 of 0 items". 
  *
  */
function get_pagination_string(
        $page = 1, 
        $total_items, 
        $limit = 8, 
        $ajax='', 
        $target_page = '', 
        $page_string = '?page=', 
        $summary_name = 'items', 
        $placement = 'summary'
        )
{
    // DEFAULTS
    $adjacents = 5;

    // HTML
    $div_bar = '<div class="bar clearfix %s_bar">';
    $display = '<div class="summary" style="width: 40%%; float: left;">Displaying %d-%d of %d %s</div>';
    $ul = '<ul id="pag_nav_links" class="pagerpro">';
    $link = '<li><a href="%s%s%s">%s</a></li>';
    $current_link = '<li class="current"><a href="%s%s%s">%s</a></li>';
    $ajax_link = '<li><a href="#" clickrewriteurl="%s&page=%s" clickrewriteid="%s" clickrewriteform="%s" clicktoshow="%s" clicktohide="%s">%s</a></li>';

//    $ajax_link = '<li><a href="#" onclick="Animation(document.getElementById(\'ttable\')).by(\'left\', \'-800px\').to(\'opacity\', 0).duration(500).go(); return false;" clickrewriteurl="%s&page=%s" clickrewriteid="%s" clickrewriteform="%s" clicktoshow="%s" clicktohide="%s">%s</a></li>';
    $current_ajax_link = '<li class="current"><a href="#" clickrewriteurl="%s&page=%s" clickrewriteid="%s" clickrewriteform="%s" clicktoshow="%s" clicktohide="%s">%s</a></li>';
    $ul_close = '</ul>';
    $div_close = '</div>';
    $div_font = '<div style="font-size:11px;">%s</div>';


    // VARS
    if(substr($page_string, 0, 1) != '?') 
        $page_string = '?' . $page_string;
    if(substr($page_string, strlen($page_string) - 5) != 'page=') 
        $page_string .= '&page=';
    $prev = $page - 1;
    $next = $page + 1;
    $firstpage = 1;
    $lastpage = ceil($total_items / $limit);
	$fiop = 0;
	if ($total_items > 0) $fiop = ($limit * $page) - $limit + 1;
    $liop = min($limit * $page, $total_items);

    // DRAW PAGINATOR 

    // the footer paginator has no summary and the current page has a different gfx
    $type = ($placement == 'summary') ? 'summary' : 'footer';

    $pagination = sprintf($div_bar, $type);

    // Draw summary
    if($placement == 'summary')
        $pagination .= sprintf($display, $fiop, $liop, $total_items, $summary_name);

    if($lastpage > 1)
    {    
        $pagination .= $ul;

        // First page selector
        if ($page > 2) 
        {
            if(!empty($ajax))
            {
                $pagination .= sprintf(
                        $ajax_link,
                        $ajax['rewriteurl'],
                        $firstpage, 
                        $ajax['rewriteid'],
                        $ajax['rewriteform'],
                        $ajax['loadingimg'],
						$ajax['hide'],
                        'First');
            }
            else
            {
                $pagination .= sprintf(
                        $link, 
                        $target_page, 
                        $page_string, 
                        $firstpage, 
                        'First');
            }
        }

        // Previous page selector
        if ($page > 1) 
        {
            if(!empty($ajax))
            {
                $pagination .= sprintf(
                        $ajax_link,
                        $ajax['rewriteurl'],
                        $prev,
                        $ajax['rewriteid'],
                        $ajax['rewriteform'],
                        $ajax['loadingimg'],
						$ajax['hide'],
                        'Prev');
            }
            else
            {
                $pagination .= sprintf(
                        $link, 
                        $target_page, 
                        $page_string, 
                        $prev, 
                        'Prev');
            }
        }

        // Page selectors
        if ($page < 4)
        {    
            for ($counter = 1; $counter <= min(5, $lastpage); $counter++)
            {
                if ($counter == $page)
                {
                    if(!empty($ajax))
                    {
                        $pagination .= sprintf(
                                $current_ajax_link,
                                $ajax['rewriteurl'],
                                $counter,
                                $ajax['rewriteid'],
                                $ajax['rewriteform'],
                                $ajax['loadingimg'],
								$ajax['hide'],
                                $counter);
                    }
                    else
                    {
                        $pagination .= sprintf(
                                $current_link, 
                                $target_page, 
                                $page_string,
                                $counter,
                                $counter);
                    }
                }
                else
                {
                    if(!empty($ajax))
                    {
                        $pagination .= sprintf(
                                $ajax_link,
                                $ajax['rewriteurl'],
                                $counter,
                                $ajax['rewriteid'],
                                $ajax['rewriteform'],
                                $ajax['loadingimg'],
								$ajax['hide'],
                                $counter);
                    }
                    else
                    {
                        $pagination .= sprintf(
                                $link, 
                                $target_page, 
                                $page_string,
                                $counter,
                                $counter);
                    }                  
                }
            }
        }
        elseif ($page > $lastpage - 3)
        {
			// $pagination .= pre_debug('pagination: page, lastpage:', $page .', '. $lastpage);
            for($counter = $lastpage - min(5, $lastpage); 
                    $counter <= $lastpage; 
                    $counter++)
            {
				// This next line is a total hack to prevent zeros from showing up 
				// in the page links.  Should figure out why it's happening...
				// Seems like it's when there are four pages or less?
			    if ($counter < 1) $counter=1;
                if ($counter == $page)
                {
                    if(!empty($ajax))
                    {
                        $pagination .= sprintf(
                                $current_ajax_link,
                                $ajax['rewriteurl'],
                                $counter,
                                $ajax['rewriteid'],
                                $ajax['rewriteform'],
                                $ajax['loadingimg'],
								$ajax['hide'],
                                $counter);
                    }
                    else
                    {
                        $pagination .= sprintf(
                                $current_link, 
                                $target_page, 
                                $page_string,
                                $counter,
                                $counter);
                    }                    
                }
                else
                {
                    if(!empty($ajax))
                    {
                        $pagination .= sprintf(
                                $ajax_link,
                                $ajax['rewriteurl'],
                                $counter,
                                $ajax['rewriteid'],
                                $ajax['rewriteform'],
                                $ajax['loadingimg'],
								$ajax['hide'],
                                $counter);
                    }
                    else
                    {
                        $pagination .= sprintf(
                                $link, 
                                $target_page, 
                                $page_string,
                                $counter,
                                $counter);
                    }                    
                }
            }
        }
        else
        {
            for($counter = $page - 2; $counter <= $page + 2; $counter++)
            {
                if ($counter == $page)
                {
                    if(!empty($ajax))
                    {
                        $pagination .= sprintf(
                                $current_ajax_link,
                                $ajax['rewriteurl'],
                                $counter,
                                $ajax['rewriteid'],
                                $ajax['rewriteform'],
                                $ajax['loadingimg'],
								$ajax['hide'],
                                $counter);
                    }
                    else
                    {
                        $pagination .= sprintf(
                                $current_link, 
                                $target_page, 
                                $page_string,
                                $counter,
                                $counter);
                    }                    
                }
                else
                {
                    if(!empty($ajax))
                    {
                        $pagination .= sprintf(
                                $ajax_link,
                                $ajax['rewriteurl'],
                                $counter,
                                $ajax['rewriteid'],
                                $ajax['rewriteform'],
                                $ajax['loadingimg'],
								$ajax['hide'],
                                $counter);
                    }
                    else
                    {
                        $pagination .= sprintf(
                                $link, 
                                $target_page, 
                                $page_string,
                                $counter,
                                $counter);
                    }                    
                }
            }
        }

        //next button
        if ($page < $lastpage) 
        {

            if(!empty($ajax))
            {
                $pagination .= sprintf(
                        $ajax_link,
                        $ajax['rewriteurl'],
                        $next,
                        $ajax['rewriteid'],
                        $ajax['rewriteform'],
                        $ajax['loadingimg'],
						$ajax['hide'],
                        'Next');
            }
            else
            {
                $pagination .= sprintf(
                        $link,
                        $target_page,
                        $page_string,
                        $next,
                        'Next');
            }                       

        }

        //last button
        if ($page < $lastpage - 1) 
        {
            if(!empty($ajax))
            {
                $pagination .= sprintf(
                        $ajax_link,
                        $ajax['rewriteurl'],
                        $lastpage,
                        $ajax['rewriteid'],
                        $ajax['rewriteform'],
                        $ajax['loadingimg'],
						$ajax['hide'],
                        'Last');
            }
            else
            {
                $pagination .= sprintf(
                        $link,
                        $target_page,
                        $page_string,
                        $lastpage,
                        'Last');
            }                   
        }
        $pagination .= $ul_close;
    }

    $pagination .= $div_close;
    $pagination = sprintf($div_font, $pagination);

    return $pagination;
}
