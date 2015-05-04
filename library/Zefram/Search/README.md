# Zefram\_Search

This package provides enhancements and fixes to `Zend_Search`.

## Zefram\_Search\_Lucene

Implementation of `Zend_Search_Lucene` has the following design flaw - the analyzer is shared among all Lucene instances across the application.

`Zend_Search_Lucene` instance, when saving and retrieving data, always uses the default analyzer instance. This may lead to unexpected or corrupted search index state if the default analyzer changes, or there is more than one index in the application, and each of them is expecting a different analyzer.

Ideally Lucene instances, once created, should be agnostic to any further changes of the default analyzer. Currently one has to call `Zend_Search_Lucene_Analysis_Analyzer::setDefault()` every time before writing to index or querying the index, to ensure that the proper analyzer instance is used, which is somewhat inconvenient.

To overcome this a replacement for `Zend_Search_Lucene` class named `Zefram_Search_Lucene` is provided. It inherits from `Zend_Search_Lucene`, but ensures that `addDocument()` and `find()` methods always use the same analyzer - the one which was set as default when the index object was created.

```php
Zend_Search_Lucene_Analysis_Analyzer::setDefault(
    new Zend_Search_Lucene_Analysis_Analyzer_Common_Text()
);

// use Zefram_Search_Lucene::open() if index already exists
$index = Zefram_Search_Lucene::create('path/to/index');
```

## Analyzer

Analyzers shipped with `Zend_Search_Lucene` share almost the same code (with only tiny differences), which make them harder to maintain and extend.

Not only to eliminate code duplication, but also because of the need to extend them, all of their functionality has been gathered in a single class, `Zefram_Search_Lucene_Analysis_Analyzer`, which can behave as any of the built-in analyzers just by setting the proper `encoding`, `tokenizeNumbers` and `filters` options.

But most important feature of the provided analyzer implementation is that `filters` can be provided not only as instances of `Zend_Search_Lucene_Analysis_TokenFilter`, but also as an array, similar to how elements can be provided to `Zend_Form` or validators to `Zend_Form_Element`.

Each filter can be given in one of the following forms:

- `Zend_Search_Lucene_Analysis_TokenFilter $filter`
- `string $name`
- `array('filter' => string $name, 'options' => array $options)`
- `array(string $name, array $options)`

This makes it possible for an analyzer to be defined using only a config file, e.g.:

```ini
encoding = UTF-8
tokenizeNumbers = TRUE

; filters configuration
filters.lowerCase.filter = lowerCase
filters.lowerCase.options.encoding = UTF-8

filters.stopWords.filter = stopWords
filters.stopWords.options.file = /path/to/stopwords.txt

filters.shortWords.filter = shortWords
filters.shortWords.options.encoding = UTF-8
filters.shortWords.options.minLength = 3
```

and to instantiate the analyzer using the config:

```php
$analyzerConfig = new Zend_Config_Ini('/path/to/analyzer.ini');
$analyzer = new Zefram_Search_Lucene_Analysis_Analyzer(
    $analyzerConfig->toArray()
);
```

## Token Filters

`Zefram_Search_Lucene` package provides a set of token filters, some of them can be used as replacements for respective `Zend_Search_Lucene` classes. All filters listed below have the same class prefix `Zefram_Search_Lucene_Analysis_TokenFilter_` omitted here for brevity.

- `LowerCase` filter supports multiple encodings, so there is no need for a separate `LowerCaseUtf8` filter.

- `ShordWords` filter, contrary to Zend implementation, correctly calculates length of UTF-8 encoded strings.

- `StopWords` filter supports stop words provided either as an array (`data` option) or loaded from file (`file` option). When loading from the file it properly handles UTF-8 [Byte Order Mark](https://en.wikipedia.org/wiki/Byte_order_mark#UTF-8). It also supports multiple encodings (`encoding` option), and a customizable comment start character (`commentChar` option, `"#"` by default).

- `PorterStem` - additional filter, with no Zend counterpart, for transforming input tokens according to [Porter Stemming algorithm](https://tartarus.org/martin/PorterStemmer/) using the implementation by [Richard Heyes](http://www.phpguru.org/).
