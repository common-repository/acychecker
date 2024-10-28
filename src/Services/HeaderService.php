<?php

namespace AcyChecker\Services;


use AcyChecker\Libraries\AcycObject;
use AcyCheckerCmsServices\Language;
use AcyCheckerCmsServices\Security;

class HeaderService extends AcycObject
{
    public function display($breadcrumb)
    {
        $header = '<div class="cell large-5 xlarge-7 xxlarge-8 grid-x acyc_vcenter">';
        $header .= $this->getBreadcrumb($breadcrumb);
        $header .= '</div>';

        // Begin Tools
        $header .= '<div class="cell large-7 xlarge-5 xxlarge-4 grid-x align-right acyc_vcenter">';

        $header .= $this->getLicenseInfo();

        // End Tools
        $header .= '</div>';

        $header = '<div id="acyc_header" class="grid-x margin-bottom-1">'.$header.'</div>';

        return $this->getLastNews().$header;
    }

    private function getLastNews()
    {
        $lastNewsCheck = $this->config->get('last_news_check', 0);
        if ($lastNewsCheck < time() - 7200) {
            $context = stream_context_create(['http' => ['timeout' => 1]]);
            $news = @file_get_contents(ACYC_ACYMAILLING_WEBSITE.'acymnews.xml', false, $context);
            $this->config->save(
                [
                    'last_news_check' => time(),
                    'last_news' => base64_encode($news),
                ],
                false
            );
        } else {
            $news = $this->config->get('last_news', '');
            if (!empty($news)) $news = base64_decode($news);
        }
        if (empty($news)) return '';

        $news = @simplexml_load_string($news);
        if (empty($news->news)) return '';

        $currentLanguage = Language::getLanguageTag();
        $latestNews = null;
        $doNotRemind = json_decode($this->config->get('remindme', '[]'));

        foreach ($news->news as $oneNews) {
            // If we found a news more recent, it means we found the latest available one
            if (!empty($latestNews) && strtotime($latestNews->date) > strtotime($oneNews->date)) break;

            // If the news isn't published or that the language isn't correct, leave
            $language = strtolower($oneNews->language);
            if (empty($oneNews->published) || ($language != strtolower($currentLanguage) && ($language != 'default' || !empty($latestNews)))) {
                continue;
            }

            // If the extension isn't correct, leave
            if (!empty($oneNews->extension) && strtolower($oneNews->extension) != 'acychecker') continue;

            // If the CMS isn't correct, leave
            if (!empty($oneNews->cms) && strtolower($oneNews->cms) != strtolower('wordpress')) continue;

            // If the level of the extension isn't correct, leave
            if (!empty($oneNews->level) && strtolower($oneNews->level) != strtolower($this->config->get('level'))) continue;

            // If the version of the extension isn't correct, leave
            if (!empty($oneNews->version)) {
                list($version, $operator) = explode('_', $oneNews->version);
                if (!version_compare($this->config->get('version'), $version, $operator)) continue;
            }

            if (in_array($oneNews->name, $doNotRemind)) continue;

            $latestNews = $oneNews;
        }

        if (empty($latestNews)) return '';

        $newsDisplay = '<div id="acyc__header__banner__news" data-news="'.Security::escape($latestNews->name).'">';

        if (!empty($latestNews)) {
            $newsDisplay .= $latestNews->content;
        }

        $newsDisplay .= '</div>';

        return $newsDisplay;
    }

    private function getBreadcrumb($breadcrumb)
    {
        $links = [];
        foreach ($breadcrumb as $oneLevel => $link) {
            if (!empty($link)) {
                $oneLevel = '<a href="'.$link.'">'.$oneLevel.'</a>';
            }
            $links[] = '<li>'.$oneLevel.'</li>';
        }

        if (count($links) > 1) {
            $links[count($links) - 1] = str_replace('<li>', '<li class="last_link cell shrink acyc_vcenter"><span class="show-for-sr">Current: </span>', $links[count($links) - 1]);
        }

        $header = '<i class="cell shrink acyc-logo"></i>';
        $header .= '<div id="acyc_global_navigation" class="cell auto">
                        <nav aria-label="You are here:" role="navigation">
                            <ul class="breadcrumbs grid-x acyc_vcenter">'.implode('<li class="breadcrumbs__separator"><i class="acycicon-angle-right"></i></li>', $links).'</ul>
                        </nav>
                    </div>';

        return $header;
    }

    private function getLicenseInfo()
    {
        $licenseKey = $this->config->get('license_key', '');

        if (empty($licenseKey)) return '';

        $apiService = new ApiService();
        $apiService->refreshCreditsUsed();

        $licenseName = $this->config->get('license_level', '');
        $version = $this->config->get('version');

        $creditsRemainingBatch = $this->config->get('remaining_credits_batch', 0);
        $creditsRemainingSimple = $this->config->get('remaining_credits_simple', 0);
        $endDate = $this->config->get('license_end_date');
        // this is th date format : "2022-10-25 15:41:02.000000,3,Europe/Paris" we only need the first part of it.
        $endDateFomat = explode(',', $endDate)[0];

        $creditsRemaining = strpos(strtolower($licenseName), 'agency') === false ? sprintf(__('%1$s database tests remaining and %2$s registration tests remaining', 'acychecker'),
            $creditsRemainingBatch,
            $creditsRemainingSimple
        ) : __('Unlimited remaining tests', 'acychecker');

        $return = '<div class="cell shrink grid-x text-right margin-right-1">';
        $return .= '<div class="cell">'.$licenseName.' '.$version.'</div>';
        $return .= '<div class="cell">'.$creditsRemaining.'</div>';
        $return .= '<div class="cell">'.sprintf(__('Valid Until: %s', 'acychecker'), DateService::date($endDateFomat, __('m/d/Y', 'acychecker'))).'</div>';
        $return .= '</div>';

        $link = ACYC_ACYCHECKER_WEBSITE.'pricing?utm_source=acychecker_plugin&utm_campaign=upgrade_plans&utm_medium=button_upgrade_plans_header';
        $return .= '<a target="_blank" href="'.$link.'" class="button cell shrink margin-right-1">'.__('Upgrade plans', 'acychecker').'</a>';

        $return .= '<a target="_blank" href="'.ACYC_DOC_URL.'" class="button cell shrink acyc_documentation">'.__('Documentation', 'acychecker').'</a>';

        return $return;
    }
}
