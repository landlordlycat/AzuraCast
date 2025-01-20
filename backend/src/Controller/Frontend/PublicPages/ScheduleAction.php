<?php

declare(strict_types=1);

namespace App\Controller\Frontend\PublicPages;

use App\Controller\Frontend\PublicPages\Traits\IsEmbeddable;
use App\Controller\SingleActionInterface;
use App\Exception\NotFoundException;
use App\Http\Response;
use App\Http\ServerRequest;
use Psr\Http\Message\ResponseInterface;

final class ScheduleAction implements SingleActionInterface
{
    use IsEmbeddable;

    public function __invoke(
        ServerRequest $request,
        Response $response,
        array $params
    ): ResponseInterface {
        $station = $request->getStation();

        if (!$station->getEnablePublicPage()) {
            throw NotFoundException::station();
        }

        $router = $request->getRouter();

        $pageClass = 'schedule station-' . $station->getShortName();
        if ($this->isEmbedded($request, $params)) {
            $pageClass .= ' embed';
        }

        $view = $request->getView();

        // Add station public code.
        $view->fetch(
            'frontend/public/partials/station-custom',
            ['station' => $station]
        );

        return $view->renderVuePage(
            response: $response
                ->withHeader('X-Frame-Options', '*'),
            component: 'Public/Schedule',
            id: 'station-schedule',
            layout: 'minimal',
            title: __('Schedule') . ' - ' . $station->getName(),
            layoutParams: [
                'page_class' => $pageClass,
                'hide_footer' => true,
            ],
            props: [
                'scheduleUrl' => $router->named('api:stations:schedule', [
                    'station_id' => $station->getId(),
                ]),
                'stationName' => $station->getName(),
                'stationTimeZone' => $station->getTimezone(),
            ],
        );
    }
}
