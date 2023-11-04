{
  inputs = {
    nixpkgs.url = "github:NixOS/nixpkgs/nixpkgs-unstable";
    flake-utils.url = "github:numtide/flake-utils";

    nix-php-shell = {
      url = "github:loophp/nix-shell";
      inputs.nixpkgs.follows = "nixpkgs";
    };
  };

  outputs = { self, nixpkgs, flake-utils, nix-php-shell }:
    flake-utils.lib.eachDefaultSystem (system:
      let
        pkgs = import nixpkgs {
          inherit system;
          overlays = [
            nix-php-shell.overlays.default
          ];
        };
        php = pkgs.api.buildPhpFromComposer { src = self; };
      in
      {
        devShells = {
          default = pkgs.mkShell {
            packages = [
              pkgs.cachix
              php
              php.packages.composer
            ];
          };
        };
      });
}
