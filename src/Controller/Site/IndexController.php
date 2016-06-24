<?php
namespace Mapping\Controller\Site;

use Omeka\Controller\Site\AbstractSiteController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractSiteController
{
    public function browseAction()
    {
        // Get all markers in this site's item pool and render them on a map.
        $site = $this->getSite();

        $query = $this->params()->fromQuery();
        $query['site_id'] = $site->id();
        if ($this->siteSettings()->get('browse_attached_items', false)) {
            $query['site_attachments_only'] = true;
        }
        $items = $this->api()->search('items', $query)->getContent();
        $itemIds = [];
        foreach ($items as $item) {
            $itemIds[] = $item->id();
        }
        unset($items);

        $markersQuery = ['item_id' => $itemIds];
        if (isset($query['mapping_address']) && isset($query['mapping_radius'])) {
            $markersQuery['address'] = $query['mapping_address'];
            $markersQuery['radius'] = $query['mapping_radius'];
            $markersQuery['radius_unit'] = isset($query['mapping_radius_unit'])
                ? $query['mapping_radius_unit'] : null;
        }
        $response = $this->api()->search('mapping_markers', $markersQuery);
        $markers = $response->getContent();

        $view = new ViewModel;
        $view->setVariable('query', $query);
        $view->setVariable('markers', $markers);
        return $view;
    }
}
