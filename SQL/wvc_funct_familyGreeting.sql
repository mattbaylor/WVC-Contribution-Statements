USE [ArenaDB]
GO

/****** Object:  UserDefinedFunction [dbo].[wvc_funct_familyGreeting]    Script Date: 12/30/2012 11:34:53 ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO





CREATE FUNCTION [dbo].[wvc_funct_familyGreeting]
(
	@FamilyID int
)
RETURNS nvarchar(255)

AS
BEGIN
	DECLARE @Greeting nvarchar(255)
	IF (SELECT COUNT(cfm.person_id)
			FROM core_family_member cfm
			WHERE cfm.family_id = @FamilyID AND cfm.role_luid = 29) = 1
		BEGIN
			SELECT @Greeting = (
				SELECT TOP 1 
					CASE WHEN cp.first_name IS NULL OR LEN(cp.first_name) = 0 THEN
							[ArenaDB].[dbo].[wvc_funct_titleCase](cp.last_name)
						ELSE 
							cpt.lookup_value + ' ' + [ArenaDB].[dbo].[wvc_funct_titleCase](cp.first_name) + ' ' + [ArenaDB].[dbo].[wvc_funct_titleCase](cp.last_name)
					END
				FROM core_family_member cfm
				INNER JOIN core_person cp ON cp.person_id = cfm.person_id
				LEFT OUTER JOIN core_person_address cpa ON cp.person_id = cpa.person_id AND cpa.primary_address = 1
				LEFT OUTER JOIN core_address ca ON ca.address_id = cpa.address_id
				LEFT OUTER JOIN core_lookup cpt ON cp.title_luid = cpt.lookup_id
				WHERE cfm.family_id = @FamilyID AND cfm.role_luid = 29
				ORDER BY cp.gender
				)
		END
	ELSE
		BEGIN
			SELECT @Greeting =  (SELECT TOP 1 cpt.lookup_value + ' and Mrs. ' + [ArenaDB].[dbo].[wvc_funct_titleCase](cp.first_name) + ' ' + [ArenaDB].[dbo].[wvc_funct_titleCase](cp.last_name) AS [greeting]
			FROM core_family_member cfm
			INNER JOIN core_person cp ON cp.person_id = cfm.person_id
			LEFT OUTER JOIN core_person_address cpa ON cp.person_id = cpa.person_id AND cpa.primary_address = 1
			LEFT OUTER JOIN core_address ca ON ca.address_id = cpa.address_id
			LEFT OUTER JOIN core_lookup cpt ON cp.title_luid = cpt.lookup_id
			WHERE cfm.family_id = @FamilyID AND cfm.role_luid = 29
			ORDER BY cp.gender)
		END
	RETURN (@Greeting)
END


GO


