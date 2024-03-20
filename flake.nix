{
  inputs = {
    nixpkgs.url = "github:NixOS/nixpkgs/nixpkgs-unstable";
    flake-utils.url = "github:numtide/flake-utils";
    pre-commit-hooks = {
      url = "github:cachix/pre-commit-hooks.nix";
      inputs.nixpkgs.follows = "nixpkgs";
      inputs.flake-utils.follows = "flake-utils";
    };
    treefmt-nix = {
      url = "github:numtide/treefmt-nix";
      inputs.nixpkgs.follows = "nixpkgs";
    };

    nix-php-shell = {
      url = "github:loophp/nix-shell";
      inputs.nixpkgs.follows = "nixpkgs";
    };
  };

  outputs = { self, nixpkgs, flake-utils, nix-php-shell, pre-commit-hooks, treefmt-nix, ... }:
    flake-utils.lib.eachDefaultSystem (system:
      let
        pkgs = import nixpkgs {
          inherit system;
          overlays = [
            nix-php-shell.overlays.default
          ];
        };
        php = pkgs.api.buildPhpFromComposer { src = self; };
        treefmtEval = treefmt-nix.lib.evalModule pkgs {
          projectRootFile = "flake.nix";
          programs.nixpkgs-fmt.enable = true;
          programs.yamlfmt.enable = true;
        };
      in
      {
        formatter = treefmtEval.config.build.wrapper;
        checks = {
          pre-commit-check = pre-commit-hooks.lib.${system}.run {
            src = ./.;
            hooks = {
              actionlint.enable = true;
              editorconfig-checker.enable = true;
              markdownlint.enable = true;
              hadolint.enable = true;
            };
          };
          formatting = treefmtEval.config.build.check self;
        };
        devShells = {
          default = pkgs.mkShell {
            packages = [
              pkgs.cachix
              pkgs.actionlint
              pkgs.editorconfig-checker
              pkgs.markdownlint-cli
              pkgs.hadolint

              php
              php.packages.composer
            ];
            inputsFrom = [
              treefmtEval.config.build.devShell
            ];
            inherit (self.checks.${system}.pre-commit-check) shellHook;
          };
        };
      });
}
