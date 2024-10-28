<?php


namespace AcyChecker\Services;

use AcyChecker\Classes\ConfigurationClass;
use AcyCheckerCmsServices\Form;
use AcyCheckerCmsServices\Language;

class PaginationService
{
    private $page;
    private $total;
    private $perPage;
    private $pageName;

    public function __construct($page, $total, $perPage, $pageName)
    {
        $configClass = new ConfigurationClass();
        $configClass->load();
        $this->page = $page;
        $this->total = $total;
        $this->perPage = $configClass->get('listing_element_page', $perPage);
        $this->pageName = $pageName;
    }

    public function display()
    {
        // We calculate the number of pages
        $numberOfPages = ceil($this->total / $this->perPage);

        $pagination = '<div class="pagination align-center cell grid-x" role="navigation" aria-label="Pagination">
                        <div class="cell shrink margin-auto grid-x grid-margin-x align-center">
                            <div class="small-auto medium-shrink pagination_container cell grid-x acyc_vcenter align-center">';


        $pagination .= '<div class="cell shrink pagination_border_left"></div>';
        $pagination .= '<input type="number" name="'.$this->pageName.'" min="1" max="'.(empty($numberOfPages) ? 1
                : $numberOfPages).'" value="'.$this->page.'" class="cell shrink pagination_input" id="acyc_pagination_input">';
        $pagination .= '<p class="cell shrink margin-left-1 pagination_text">'.__('Out of', 'acychecker').' '.$numberOfPages.'</p>';
        $pagination .= '<div class="cell shrink pagination_border_right"></div>';

        $pagination .= '</div>';

        $elementPerPagePossible = [
            10 => 10,
            15 => 15,
            20 => 20,
            30 => 30,
            50 => 50,
            100 => 100,
            200 => 200,
            500 => 500,
        ];

        $selectElementPage = FormService::select(
            $elementPerPagePossible,
            'acyc_element_per_page',
            $this->perPage,
            ['id' => 'acyc_element_per_page', 'class' => 'cell shrink margin-bottom-0 margin-right-1 margin-left-1']
        );

        $pagination .= '<div class="cell shrink grid-x acyc_vcenter">'.sprintf(__('Display %s entries', 'acychecker'), $selectElementPage).'</div>';
        $pagination .= '</div>';

        return $pagination;
    }
}
