<?php

declare(strict_types=1);

namespace WilyCode\Filters;

use InvalidArgumentException;
use WilyCode\Filter\FilterInterface;

/** Turn a human-readable title into an ASCII slug accepted by SlugValidator. */
final class SlugFilter implements FilterInterface
{
    /**
     * Common Latin diacritics, grouped by their ASCII replacement.
     *
     * Using an explicit table avoids platform- and locale-dependent
     * iconv //TRANSLIT results (for example caf-e instead of cafe on macOS).
     * The original text is lowercased after replacement, so uppercase
     * equivalents are handled by the same groups.
     *
     * @var array<string, string>
     */
    private const array LATIN_TO_ASCII = [
        'a' => 'ÀÁÂÃÄÅàáâãäåĀāĂăĄąǍǎǞǟǠǡǺǻȀȁȂȃȦȧḀḁẠạẢảẤấẦầẨẩẪẫẬậẮắẰằẲẳẴẵẶặ',
        'ae' => 'Ææ',
        'b' => 'ƀƁƂƃḂḃḄḅḆḇ',
        'c' => 'ÇçĆćĈĉĊċČčƇƈȻȼḈḉ',
        'd' => 'ÐðĎďĐđƊƌḊḋḌḍḎḏḐḑḒḓ',
        'dz' => 'ǄǅǆǱǲǳ',
        'e' => 'ÈÉÊËèéêëĒēĔĕĖėĘęĚěƐȄȅȆȇȨȩɛḔḕḖḗḘḙḚḛḜḝẸẹẺẻẼẽẾếỀềỂểỄễỆệ',
        'f' => 'ƑƒḞḟ',
        'g' => 'ĜĝĞğĠġĢģǦǧǴǵḠḡ',
        'h' => 'ĤĥĦħȞȟḢḣḤḥḦḧḨḩḪḫẖ',
        'hv' => 'ƕ',
        'i' => 'ÌÍÎÏìíîïĨĩĪīĬĭĮįİıǏǐȈȉȊȋḬḭḮḯỈỉỊị',
        'ij' => 'Ĳĳ',
        'j' => 'Ĵĵǰȷ',
        'k' => 'ĶķĸǨǩḰḱḲḳḴḵ',
        'l' => 'ĹĺĻļĽľĿŀŁłƚȽḶḷḸḹḺḻḼḽ',
        'lj' => 'Ǉǈǉ',
        'm' => 'ḾḿṀṁṂṃ',
        'n' => 'ÑñŃńŅņŇňŊŋǸǹṄṅṆṇṈṉṊṋ',
        'nj' => 'Ǌǋǌ',
        'o' => 'ÒÓÔÕÖØòóôõöøŌōŎŏŐőƆƠơǑǒǪǫǬǭȌȍȎȏȪȫȬȭȮȯȰȱɔṌṍṎṏṐṑṒṓỌọỎỏỐốỒồỔổỖỗỘộỚớỜờỞởỠỡỢợ',
        'oe' => 'Œœ',
        'p' => 'ṔṕṖṗ',
        'r' => 'ŔŕŖŗŘřȐȑȒȓṘṙṚṛṜṝṞṟ',
        's' => 'ŚśŜŝŞşŠšſȘșṠṡṢṣṤṥṦṧṨṩẛ',
        'ss' => 'ßẞ',
        't' => 'ŢţŤťŦŧƬƭȚțṪṫṬṭṮṯṰṱẗ',
        'th' => 'Þþ',
        'u' => 'ÙÚÛÜùúûüŨũŪūŬŭŮůŰűŲųƯưǓǔǕǖǗǘǙǚǛǜȔȕȖȗṲṳṴṵṶṷṸṹṺṻỤụỦủỨứỪừỬửỮữỰự',
        'v' => 'ṼṽṾṿ',
        'w' => 'ŴŵẀẁẂẃẄẅẆẇẈẉẘ',
        'x' => 'ẊẋẌẍ',
        'y' => 'ÝýÿŶŷŸƳƴȲȳẎẏẙỲỳỴỵỶỷỸỹ',
        'z' => 'ŹźŻżŽžƵƶẐẑẒẓẔẕ',
    ];

    public function getFilteredValue(mixed $value): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        if (preg_match('//u', $value) !== 1) {
            throw new InvalidArgumentException('SlugFilter expects a valid UTF-8 string.');
        }

        $value = strtr($value, self::transliterationTable());

        // Combining accents in decomposed Unicode must not turn into hyphens.
        $value = preg_replace('/\p{M}+/u', '', $value) ?? $value;
        $value = strtolower($value);

        // Existing repeated hyphens remain valid: my--article.
        $value = preg_replace('/\s*-\s*/u', '-', $value) ?? $value;
        $slug = preg_replace('/[^a-z0-9-]+/', '-', $value);

        return trim($slug ?? '', '-');
    }

    /** @return array<string, string> */
    private static function transliterationTable(): array
    {
        static $table = null;

        if ($table === null) {
            $table = [];
            foreach (self::LATIN_TO_ASCII as $replacement => $letters) {
                foreach (preg_split('//u', $letters, -1, PREG_SPLIT_NO_EMPTY) as $letter) {
                    $table[$letter] = $replacement;
                }
            }
        }

        return $table;
    }
}
