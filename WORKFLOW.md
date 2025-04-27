# Workflow of PHP Packer Parser

```mermaid
graph TD;
    A[Start: Entry PHP File] --> B[ParserFactory::create];
    B --> C[CodeParser::parse];
    C --> D[AST Parsing via AstCodeParser];
    D --> E[Dependency Analysis];
    E --> F{Has Dependencies?};
    F -- Yes --> G[Recursively parse dependencies];
    F -- No --> H[Collect Processed Files & Dependencies];
    G --> H;
    H --> I[Return Results];
```

## Explanation

- The parser is created via `ParserFactory::create` with entry file and options.
- `CodeParser::parse` is called for the entry file.
- The file is parsed to AST, then dependencies are analyzed.
- Each dependency is recursively parsed.
- The process collects all parsed files and their dependencies.
- Results can be accessed via `getProcessedFiles()` and `getDependencies()`.
