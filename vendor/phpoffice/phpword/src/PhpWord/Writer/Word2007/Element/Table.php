<?php
/**
 * This file is part of PHPWord - A pure PHP library for reading and writing
 * word processing documents.
 *
 * PHPWord is free software distributed under the terms of the GNU Lesser
 * General Public License version 3 as published by the Free Software Foundation.
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code. For the full list of
 * contributors, visit https://github.com/PHPOffice/PHPWord/contributors.
 *
 * @link        https://github.com/PHPOffice/PHPWord
 * @copyright   2010-2016 PHPWord contributors
 * @license     http://www.gnu.org/licenses/lgpl.txt LGPL version 3
 */

namespace PhpOffice\PhpWord\Writer\Word2007\Element;

use PhpOffice\Common\XMLWriter;
use PhpOffice\PhpWord\Element\Cell as CellElement;
use PhpOffice\PhpWord\Element\Row as RowElement;
use PhpOffice\PhpWord\Element\Table as TableElement;
use PhpOffice\PhpWord\Style\Cell as CellStyle;
use PhpOffice\PhpWord\Style\Row as RowStyle;
use PhpOffice\PhpWord\Writer\Word2007\Style\Cell as CellStyleWriter;
use PhpOffice\PhpWord\Writer\Word2007\Style\Row as RowStyleWriter;
use PhpOffice\PhpWord\Writer\Word2007\Style\Table as TableStyleWriter;

/**
 * Table element writer
 *
 * @since 0.10.0
 */
class Table extends AbstractElement
{
    /**
     * Write element.
     *
     * @return void
     */
    public function write()
    {
        $xmlWriter = $this->getXmlWriter();
        $element = $this->getElement();
        if (!$element instanceof TableElement) {
            return;
        }

        $rows = $element->getRows();
        $rowCount = count($rows);

        if ($rowCount > 0) {
            $xmlWriter->startElement('w:tbl');

            // Write columns
            $this->writeColumns($xmlWriter, $element);

            // Write style
            $styleWriter = new TableStyleWriter($xmlWriter, $element->getStyle());
            $styleWriter->setWidth($element->getWidth());
            $styleWriter->write();

            // Write rows
            for ($i = 0; $i < $rowCount; $i++) {
                $this->writeRow($xmlWriter, $rows[$i]);
            }

            $xmlWriter->endElement(); // w:tbl
        }
    }

    /**
     * Write column.
     *
     * @return void
     */
    private function writeColumns(XMLWriter $xmlWriter, TableElement $element)
    {
        $rows = $element->getRows();
        $rowCount = count($rows);

        $cellWidths = [];
        for ($i = 0; $i < $rowCount; $i++) {
            $row = $rows[$i];
            $cells = $row->getCells();
            if (count($cells) <= count($cellWidths)) {
                continue;
            }
            $cellWidths = [];
            foreach ($cells as $cell) {
                $cellWidths[] = $cell->getWidth();
            }
        }

        $xmlWriter->startElement('w:tblGrid');
        foreach ($cellWidths as $width) {
            $xmlWriter->startElement('w:gridCol');
            if (null !== $width) {
                $xmlWriter->writeAttribute('w:w', $width);
                $xmlWriter->writeAttribute('w:type', 'dxa');
            }
            $xmlWriter->endElement();
        }
        $xmlWriter->endElement(); // w:tblGrid
    }

    /**
     * Write row.
     *
     * @return void
     */
    private function writeRow(XMLWriter $xmlWriter, RowElement $row)
    {
        $xmlWriter->startElement('w:tr');

        // Write style
        $rowStyle = $row->getStyle();
        if ($rowStyle instanceof RowStyle) {
            $styleWriter = new RowStyleWriter($xmlWriter, $rowStyle);
            $styleWriter->setHeight($row->getHeight());
            $styleWriter->write();
        }

        // Write cells
        foreach ($row->getCells() as $cell) {
            $this->writeCell($xmlWriter, $cell);
        }

        $xmlWriter->endElement(); // w:tr
    }

    /**
     * Write cell.
     *
     * @return void
     */
    private function writeCell(XMLWriter $xmlWriter, CellElement $cell)
    {
        $xmlWriter->startElement('w:tc');

        // Write style
        $cellStyle = $cell->getStyle();
        if ($cellStyle instanceof CellStyle) {
            $styleWriter = new CellStyleWriter($xmlWriter, $cellStyle);
            $styleWriter->setWidth($cell->getWidth());
            $styleWriter->write();
        }

        // Write content
        $containerWriter = new Container($xmlWriter, $cell);
        $containerWriter->write();

        $xmlWriter->endElement(); // w:tc
    }
}
