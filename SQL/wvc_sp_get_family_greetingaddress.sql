USE [ArenaDB]
GO

/****** Object:  StoredProcedure [dbo].[wvc_sp_get_family_GreetingAddress]    Script Date: 12/30/2012 11:41:29 ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO






CREATE              PROC [dbo].[wvc_sp_get_family_GreetingAddress]
@FamilyID int

AS

IF (SELECT COUNT(cfm.person_id)
		FROM core_family_member cfm
		WHERE cfm.family_id = @FamilyID AND cfm.role_luid = 29) = 1
	BEGIN
		SELECT TOP 1 cpt.lookup_value + ' ' + cp.first_name + ' ' + cp.last_name AS [greeting],
			ca.street_address_1, ca.street_address_2, ca.city, ca.state, ca.postal_code
		FROM core_family_member cfm
		INNER JOIN core_person cp ON cp.person_id = cfm.person_id
		LEFT OUTER JOIN core_person_address cpa ON cp.person_id = cpa.person_id AND cpa.primary_address = 1
		LEFT OUTER JOIN core_address ca ON ca.address_id = cpa.address_id
		LEFT OUTER JOIN core_lookup cpt ON cp.title_luid = cpt.lookup_id
		WHERE cfm.family_id = @FamilyID AND cfm.role_luid = 29
		ORDER BY cp.gender
	END
ELSE
	BEGIN
		SELECT TOP 1 'Mr. and Mrs. ' + cp.first_name + ' ' + cp.last_name AS [greeting],
			ca.street_address_1, ca.street_address_2, ca.city, ca.state, ca.postal_code
		FROM core_family_member cfm
		INNER JOIN core_person cp ON cp.person_id = cfm.person_id
		LEFT OUTER JOIN core_person_address cpa ON cp.person_id = cpa.person_id AND cpa.primary_address = 1
		LEFT OUTER JOIN core_address ca ON ca.address_id = cpa.address_id
		LEFT OUTER JOIN core_lookup cpt ON cp.title_luid = cpt.lookup_id
		WHERE cfm.family_id = @FamilyID AND cfm.role_luid = 29
		ORDER BY cp.gender
	END

GO

