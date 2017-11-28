<?php

namespace Jh\CoreBugSimpleXmlWidget\Model\Widget;

use Magento\Framework\View\Element\AbstractBlock;
use Magento\Widget\Model\Widget\Instance as WidgetInstance;

/**
 * @author Leo Gumbo <leo@wearejh.com>
 */
class Instance extends WidgetInstance
{
    /**
     * Generate layout update xml
     *
     * @param string $container
     * @param string $templatePath
     * @return string
     */
    public function generateLayoutUpdateXml($container, $templatePath = '')
    {
        $templateFilename = $this->_viewFileSystem->getTemplateFileName(
            $templatePath,
            [
                'area'    => $this->getArea(),
                'themeId' => $this->getThemeId(),
                'module'  => AbstractBlock::extractModuleName($this->getType())
            ]
        );

        if (!$this->getId() && !$this->isCompleteToCreate() || $templatePath && !is_readable($templateFilename)) {
            return '';
        }

        $parameters = $this->getWidgetParameters();
        $xml        = '<body><referenceContainer name="' . $container . '">';
        $template   = '';

        if (isset($parameters['template'])) {
            unset($parameters['template']);
        }

        if ($templatePath) {
            $template = ' template="' . $templatePath . '"';
        }

        $hash = $this->mathRandom->getUniqueHash();
        $xml .= '<block class="' . $this->getType() . '" name="' . $hash . '"' . $template . '>';

        foreach ($parameters as $name => $value) {
            if ($name == 'conditions') {
                $name  = 'conditions_encoded';
                $value = $this->conditionsHelper->encode($value);
            } elseif (is_array($value)) {
                $value = implode(',', $value);
            }

            if ($name && strlen((string)$value)) {
                $value = html_entity_decode($value);
                $xml .= '<action method="setData">' .
                    '<argument name="name" xsi:type="string">' .
                    $name .
                    '</argument>' .
                    '<argument name="value" xsi:type="string">' .
                    $this->_escaper->escapeHtml(
                        $value
                    ) . '</argument>' . '</action>';
            }
        }

        $xml .= '</block></referenceContainer></body>';

        return $xml;
    }
}
