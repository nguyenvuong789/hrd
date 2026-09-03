# Deferred Work

- Building locale fallback: `hrd_get_building_content()` currently lets an explicitly stored empty localized field overwrite the English value before the template applies its generic empty state. Review the merge so each empty localized field falls back to the corresponding English field, as required by the building content model. This predates the shared-map change and should be fixed separately.
