<?php

declare(strict_types=1);

/*
 * This file is part of the behat/helpers project.
 *
 * (c) Ekino
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ekino\BehatHelpers;

use Behat\Mink\Element\NodeElement;

/**
 * @author Benoit Mazière <benoit.maziere@ekino.com>
 */
trait MaildevTrait
{
    /**
     * @var string
     */
    private $mailDevHost;

    public function setMailDevHost(string $mailDevHost): void
    {
        $this->mailDevHost = sprintf('http://%s', gethostbyname($mailDevHost));
    }

    /**
     * @Then /^I should see latest email unread with title "(?P<title>[^"]*)"?$/
     */
    public function iShouldSeeMostRecentEmailUnreadWithTitle(string $title): void
    {
        $this->visitMaildev();
        $this->assertElementContainsText('body > div > div.sidebar > div.sidebar-scrollable-content > ul > li:nth-child(1) > a > span.title.ng-binding', $title);
    }

    /**
     * @Then /^I browse latest received email?$/
     */
    public function iBrowseLastReceivedEmail(): void
    {
        $this->visitMaildev();
        $this->clickElement('body > div > div.sidebar > div.sidebar-scrollable-content > ul > li:nth-child(1) > a > span.title.ng-binding');
        $this->iWaitForCssElementBeingVisible('body > div > div.main-container > div.email-container.ng-scope > div', 2);

        /** @var NodeElement $iframe */
        $iframe = $this->getSession()->getPage()->find('css', '.preview-iframe');
        $this->getSession()->visit($this->mailDevHost.'/'.$iframe->getAttribute('src'));
    }

    public function getEmailContent(): string
    {
        return $this->getSession()->getPage()->find('css', '.container-padding')->getText();
    }

    private function visitMaildev(): void
    {
        $this->getSession()->visit($this->mailDevHost);
        $this->loadJQuery();
    }

    /**
     * Load missing jquery librairy to interact with page throough selectors.
     */
    private function loadJQuery(): void
    {
        $function = <<<JS
(function() {
    var script = document.createElement('script'); 
    document.head.appendChild(script);    
    script.type = 'text/javascript';
    script.src = "//ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js";
})();
JS;

        try {
            $this->getSession()->executeScript($function);
            $this->getSession()->wait(2000);
        } catch (Exception $e) {
            throw new \RuntimeException('Can not load jQuery');
        }
    }
}
